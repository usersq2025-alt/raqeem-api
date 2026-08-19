<?php

namespace App\Services;

use App\Exceptions\Gameplay\AttemptAlreadyCompletedException;
use App\Exceptions\Gameplay\BatteryRechargingException;
use App\Exceptions\Gameplay\LessonNotYetFinishedException;
use App\Exceptions\Gameplay\PreviousLessonNotCompletedException;
use App\Exceptions\Gameplay\QuestionNotInAttemptException;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameType;
use App\Models\Lesson;
use App\Models\PointsTransaction;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\StudentLessonAttempt;
use App\Services\Grading\AnswerGraderFactory;
use Illuminate\Support\Facades\DB;

class LessonAttemptService
{
    // B5 + B6.4 — بدء/استئناف محاولة درس
    public function startOrResume(Student $student, Lesson $lesson): StudentLessonAttempt
    {
        $this->assertPreviousLessonCompleted($student, $lesson);

        $existing = StudentLessonAttempt::where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->where('status', '!=', 'completed')
            ->latest('id')
            ->first();

        if ($existing) {
            $this->assertNotRecharging($existing);

            return $existing->fresh();
        }

        $nextAttemptNumber = StudentLessonAttempt::where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->max('attempt_number');

        $first = $this->firstGameQuestion($lesson);

        return StudentLessonAttempt::create([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
            'attempt_number' => ($nextAttemptNumber ?? 0) + 1,
            'status' => 'in_progress',
            'correct_count' => 0,
            'wrong_count' => 0,
            'points_earned' => 0,
            'current_game_id' => $first?->game_id,
            'current_question_id' => $first?->question_id,
            'started_at' => now(),
        ]);
    }

    // B6.1 + B6.2 + B6.3 — تسليم إجابة، تصحيح سيرفري، تحديث البطارية
    public function submitAnswer(StudentLessonAttempt $attempt, int $questionId, mixed $selectedAnswer): StudentAnswer
    {
        if ($attempt->status === 'completed') {
            throw new AttemptAlreadyCompletedException();
        }

        $this->assertNotRecharging($attempt);
        $attempt->refresh();

        $gameQuestion = GameQuestion::where('question_id', $questionId)
            ->whereHas('game', fn ($q) => $q->where('lesson_id', $attempt->lesson_id))
            ->first();

        if (! $gameQuestion) {
            throw new QuestionNotInAttemptException();
        }

        $question = Question::findOrFail($questionId);
        $gameType = GameType::findOrFail($question->game_type_id);
        $grader = AnswerGraderFactory::forGameTypeCode($gameType->code);
        $isCorrect = $grader->isCorrect($question->payload ?? [], $selectedAnswer);

        return DB::transaction(function () use ($attempt, $gameQuestion, $questionId, $selectedAnswer, $isCorrect) {
            $answer = StudentAnswer::create([
                'attempt_id' => $attempt->id,
                'game_id' => $gameQuestion->game_id,
                'question_id' => $questionId,
                'is_correct' => $isCorrect,
                'selected_answer' => $selectedAnswer,
                'answered_at' => now(),
            ]);

            $attempt->correct_count += $isCorrect ? 1 : 0;
            $attempt->wrong_count += $isCorrect ? 0 : 1;

            $totalQuestions = $this->totalQuestionsForLesson($attempt->lesson);
            $wrongRatio = $totalQuestions > 0 ? $attempt->wrong_count / $totalQuestions : 0;

            if ($wrongRatio >= config('game_rules.battery.depleted_at_wrong_ratio')) {
                $attempt->status = 'battery_depleted';
                $attempt->recharge_ends_at = now()->addMinutes(config('game_rules.battery.recharge_minutes'));
            } else {
                $next = $this->nextGameQuestion($attempt->lesson, $gameQuestion);
                $attempt->current_game_id = $next?->game_id ?? $gameQuestion->game_id;
                $attempt->current_question_id = $next?->question_id ?? $questionId;
            }

            $attempt->save();

            return $answer;
        });
    }

    // B7 — إتمام الدرس: نقاط + نجوم + تحديث الرصيد بمعاملة ذرّية واحدة
    public function complete(StudentLessonAttempt $attempt): StudentLessonAttempt
    {
        if ($attempt->status === 'completed') {
            throw new AttemptAlreadyCompletedException();
        }

        $this->assertNotRecharging($attempt);
        $attempt->refresh();

        $lesson = $attempt->lesson;
        $totalQuestions = $this->totalQuestionsForLesson($lesson);
        $answeredCount = $attempt->correct_count + $attempt->wrong_count;

        if ($totalQuestions === 0 || $answeredCount < $totalQuestions) {
            throw new LessonNotYetFinishedException($answeredCount, $totalQuestions);
        }

        return DB::transaction(function () use ($attempt, $totalQuestions) {
            $isFirstAttempt = $attempt->attempt_number === 1;
            $correctRatio = $totalQuestions > 0 ? $attempt->correct_count / $totalQuestions : 0;

            // +0 نقطة دائمًا لإعادة اللعب (attempt_number > 1) كما تشترط الوثيقة حرفيًا
            $pointsEarned = $isFirstAttempt
                ? $attempt->correct_count * config('game_rules.points.per_correct_answer')
                : 0;

            $stars = $this->calculateStars($correctRatio);

            if (! $isFirstAttempt) {
                // النجوم لا تنخفض عند إعادة لعب أضعف — تُحدَّث فقط إن كانت أفضل من الأعلى المسجَّل سابقًا
                $bestStarsSoFar = (int) StudentLessonAttempt::where('student_id', $attempt->student_id)
                    ->where('lesson_id', $attempt->lesson_id)
                    ->where('status', 'completed')
                    ->max('stars');

                $stars = max($stars, $bestStarsSoFar);
            }

            $attempt->status = 'completed';
            $attempt->completed_at = now();
            $attempt->stars = $stars;
            $attempt->points_earned = $pointsEarned;
            $attempt->save();

            if ($pointsEarned > 0) {
                PointsTransaction::create([
                    'student_id' => $attempt->student_id,
                    'type' => 'lesson_complete',
                    'points_change' => $pointsEarned,
                    'reference_type' => 'student_lesson_attempts',
                    'reference_id' => $attempt->id,
                ]);

                Student::where('id', $attempt->student_id)->increment('points_balance', $pointsEarned);
            }

            return $attempt->fresh();
        });
    }

    public function totalQuestionsForLesson(Lesson $lesson): int
    {
        return GameQuestion::whereIn(
            'game_id',
            Game::where('lesson_id', $lesson->id)->where('status', 'published')->pluck('id')
        )->count();
    }

    // لأجل الاستجابة فقط (شريط بطارية بـ 3 أقسام)، لا يُخزَّن كعمود منفصل
    public function batterySegmentsRemaining(StudentLessonAttempt $attempt, int $totalQuestions): int
    {
        $ratio = $totalQuestions > 0 ? $attempt->wrong_count / $totalQuestions : 0;
        $segments = config('game_rules.battery.total_segments');

        if ($ratio >= config('game_rules.battery.depleted_at_wrong_ratio')) {
            return 0;
        }

        if ($ratio >= config('game_rules.battery.segment_2_lost_at_wrong_ratio')) {
            return $segments - 2;
        }

        if ($ratio >= config('game_rules.battery.segment_1_lost_at_wrong_ratio')) {
            return $segments - 1;
        }

        return $segments;
    }

    private function assertPreviousLessonCompleted(Student $student, Lesson $lesson): void
    {
        $previousLesson = Lesson::where('unit_id', $lesson->unit_id)
            ->where('sort_order', '<', $lesson->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if (! $previousLesson) {
            return;
        }

        $completed = StudentLessonAttempt::where('student_id', $student->id)
            ->where('lesson_id', $previousLesson->id)
            ->where('status', 'completed')
            ->exists();

        if (! $completed) {
            throw new PreviousLessonNotCompletedException();
        }
    }

    private function assertNotRecharging(StudentLessonAttempt $attempt): void
    {
        if ($attempt->status !== 'battery_depleted') {
            return;
        }

        if ($attempt->recharge_ends_at && $attempt->recharge_ends_at->isFuture()) {
            throw new BatteryRechargingException(now()->diffInSeconds($attempt->recharge_ends_at));
        }

        // انتهى وقت الشحن (توقيت الخادم) -> يعود قابلاً للاستئناف تلقائيًا
        $attempt->status = 'in_progress';
        $attempt->recharge_ends_at = null;
        $attempt->save();
    }

    private function firstGameQuestion(Lesson $lesson): ?GameQuestion
    {
        $firstGame = Game::where('lesson_id', $lesson->id)
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->first();

        if (! $firstGame) {
            return null;
        }

        return GameQuestion::where('game_id', $firstGame->id)->orderBy('sort_order')->first();
    }

    private function nextGameQuestion(Lesson $lesson, GameQuestion $current): ?GameQuestion
    {
        $next = GameQuestion::where('game_id', $current->game_id)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            return $next;
        }

        $currentGame = Game::find($current->game_id);

        $nextGame = Game::where('lesson_id', $lesson->id)
            ->where('status', 'published')
            ->where('sort_order', '>', $currentGame->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (! $nextGame) {
            return null;
        }

        return GameQuestion::where('game_id', $nextGame->id)->orderBy('sort_order')->first();
    }

    private function calculateStars(float $correctRatio): int
    {
        if ($correctRatio >= config('game_rules.stars.three_star_min_ratio')) {
            return 3;
        }

        if ($correctRatio >= config('game_rules.stars.two_star_min_ratio')) {
            return 2;
        }

        return 1;
    }
}
