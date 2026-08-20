<?php

namespace App\Services;

use App\Exceptions\ReviewStation\QuestionNotInReviewSessionException;
use App\Exceptions\ReviewStation\ReviewSessionAlreadyCompletedException;
use App\Models\GameType;
use App\Models\PointsTransaction;
use App\Models\Question;
use App\Models\ReviewStationQuestion;
use App\Models\ReviewStationSession;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\StudentLessonAttempt;
use App\Services\Grading\AnswerGraderFactory;
use Illuminate\Support\Facades\DB;

class ReviewStationService
{
    // B8.1 — يُستدعى من نفس معاملة LessonAttemptService::complete()، يجمع الأسئلة
    // المُخطَأ بها بهذه المحاولة داخل جلسة مراجعة واحدة لكل (طالب، وحدة) — القيد
    // الفريد uq_rss_student_unit بقاعدة البيانات (المرحلة 1) يسمح بجلسة واحدة فقط
    // طوال عمر الوحدة، لذا نُعيد استخدام الجلسة القائمة إن لم تكن مكتملة بعد.
    public function collectWrongQuestionsFromAttempt(StudentLessonAttempt $attempt): void
    {
        if ($attempt->wrong_count === 0) {
            return;
        }

        $unitId = $attempt->lesson->unit_id;

        $alreadyCompletedForUnit = ReviewStationSession::where('student_id', $attempt->student_id)
            ->where('unit_id', $unitId)
            ->where('status', 'completed')
            ->exists();

        if ($alreadyCompletedForUnit) {
            // لا مجال لجولة مراجعة ثانية لهذه الوحدة (قيد قاعدة البيانات)؛ الأخطاء اللاحقة تُهمَل هنا
            return;
        }

        $session = ReviewStationSession::firstOrCreate(
            ['student_id' => $attempt->student_id, 'unit_id' => $unitId],
            ['status' => 'pending', 'points_earned' => 0, 'started_at' => now()]
        );

        $wrongQuestionIds = StudentAnswer::where('attempt_id', $attempt->id)
            ->where('is_correct', false)
            ->pluck('question_id');

        foreach ($wrongQuestionIds as $questionId) {
            ReviewStationQuestion::firstOrCreate([
                'session_id' => $session->id,
                'question_id' => $questionId,
            ]);
        }
    }

    // B8.2 — تسليم إجابة داخل محطة المراجعة: نفس منطق التصحيح السيرفري (AnswerGraderFactory)
    public function submitAnswer(ReviewStationSession $session, int $questionId, mixed $selectedAnswer): ReviewStationQuestion
    {
        if ($session->status === 'completed') {
            throw new ReviewSessionAlreadyCompletedException();
        }

        $reviewQuestion = ReviewStationQuestion::where('session_id', $session->id)
            ->where('question_id', $questionId)
            ->first();

        if (! $reviewQuestion) {
            throw new QuestionNotInReviewSessionException();
        }

        $question = Question::findOrFail($questionId);
        $gameType = GameType::findOrFail($question->game_type_id);
        $grader = AnswerGraderFactory::forGameTypeCode($gameType->code);
        $isCorrect = $grader->isCorrect($question->payload ?? [], $selectedAnswer);

        return DB::transaction(function () use ($session, $reviewQuestion, $isCorrect) {
            $reviewQuestion->is_correct = $isCorrect;
            $reviewQuestion->answered_at = now();
            $reviewQuestion->save();

            $this->maybeCompleteSessionAndGrantBonus($session);

            return $reviewQuestion->fresh();
        });
    }

    // B8.3 — عند إجابة كل أسئلة الجلسة بشكل صحيح: نصف النقاط التي فُقدت بسبب هذه
    // الأخطاء تحديدًا (عدد الأسئلة × نقطة واحدة لكل سؤال، مقسومة على 2 floor)
    private function maybeCompleteSessionAndGrantBonus(ReviewStationSession $session): void
    {
        $session->refresh();

        if ($session->status === 'completed') {
            return;
        }

        $total = ReviewStationQuestion::where('session_id', $session->id)->count();
        $correctCount = ReviewStationQuestion::where('session_id', $session->id)->where('is_correct', true)->count();

        if ($total === 0 || $correctCount < $total) {
            return; // لم تُجَب كل الأسئلة بشكل صحيح بعد
        }

        $pointsLost = $total * config('game_rules.points.per_correct_answer');
        $bonus = intdiv($pointsLost, 2); // TODO: قاعدة التقريب (floor) قرار مؤقت يحتاج تأكيد فريق المحتوى

        $session->status = 'completed';
        $session->points_earned = $bonus;
        $session->completed_at = now();
        $session->save();

        if ($bonus > 0) {
            PointsTransaction::create([
                'student_id' => $session->student_id,
                'type' => 'review_station',
                'points_change' => $bonus,
                'reference_type' => 'review_station_sessions',
                'reference_id' => $session->id,
            ]);

            Student::where('id', $session->student_id)->increment('points_balance', $bonus);
        }
    }
}
