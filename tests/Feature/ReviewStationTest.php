<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameType;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\ParentUser;
use App\Models\PointsTransaction;
use App\Models\Question;
use App\Models\ReviewStationQuestion;
use App\Models\ReviewStationSession;
use App\Models\Student;
use App\Models\StudentGiftLog;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\UnitCompletionReward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewStationTest extends TestCase
{
    use RefreshDatabase;

    private function makeLesson(int $unitId, int $sortOrder, int $questionCount): Lesson
    {
        $mcq = GameType::firstOrCreate(['code' => 'mcq'], ['name_ar' => 'mcq', 'name_en' => 'mcq', 'is_active' => true]);
        $lesson = Lesson::create(['unit_id' => $unitId, 'title' => "درس {$sortOrder}", 'sort_order' => $sortOrder, 'status' => 'published']);
        $game = Game::create(['lesson_id' => $lesson->id, 'game_type_id' => $mcq->id, 'title' => 'لعبة', 'sort_order' => 1, 'status' => 'published']);

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = Question::create([
                'lesson_id' => $lesson->id, 'game_type_id' => $mcq->id, 'question_text' => "سؤال {$i}",
                'difficulty' => 'medium',
                'payload' => ['options' => [['id' => 'a', 'text' => 'A'], ['id' => 'b', 'text' => 'B']], 'correct_option_id' => 'a'],
                'status' => 'published', 'source' => 'manual',
            ]);
            GameQuestion::create(['game_id' => $game->id, 'question_id' => $question->id, 'sort_order' => $i]);
        }

        return $lesson;
    }

    public function test_review_station_session_created_with_exactly_wrong_questions_and_grants_half_lost_points(): void
    {
        $grade = Grade::create(['level' => 1, 'name_ar' => 'الأول', 'name_en' => 'Grade 1']);
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);

        $lesson1 = $this->makeLesson($unit->id, 1, 10);
        $lesson2 = $this->makeLesson($unit->id, 2, 1); // درس صغير لإكمال الوحدة لاحقًا

        // هدية نهاية الوحدة: 10 نقاط
        UnitCompletionReward::create(['unit_id' => $unit->id, 'reward_type' => 'points', 'points_amount' => 10]);

        $parent = ParentUser::create([
            'public_id' => 'RQMP-000001', 'full_name' => 'ولي أمر', 'email' => 'parent@example.com',
            'password_hash' => Hash::make('Password123'), 'status' => 'active',
        ]);
        $student = Student::create([
            'public_id' => 'RQMS-000001', 'parent_id' => $parent->id, 'full_name' => 'طالب',
            'birth_date' => '2016-01-01', 'grade_id' => $grade->id, 'gender' => 'male',
        ]);
        $token = $parent->createToken('t')->plainTextToken;

        $attempt = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson1->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $questions = $lesson1->questions()->orderBy('id')->get();

        // 7 صحيحة + 3 خاطئة (الأسئلة بالفهرس 7،8،9 هي الخاطئة)
        foreach ($questions as $i => $q) {
            $wrong = $i >= 7;
            $this->withToken($token)
                ->postJson("/api/attempts/{$attempt['id']}/answer", [
                    'question_id' => $q->id,
                    'selected_answer' => ['selected_option_id' => $wrong ? 'b' : 'a'],
                ])
                ->assertStatus(201);
        }

        $wrongQuestionIds = $questions->slice(7, 3)->pluck('id')->sort()->values();

        $completeResp = $this->withToken($token)
            ->postJson("/api/attempts/{$attempt['id']}/complete")
            ->assertStatus(200)
            ->json();

        $this->assertSame(7, $completeResp['points_earned']); // 7 صحيحة أول محاولة = 7 نقاط

        // جلسة مراجعة واحدة بالضبط، بـ3 أسئلة بالضبط تطابق الأسئلة المُخطَأ بها بالضبط
        $this->assertSame(1, ReviewStationSession::where('student_id', $student->id)->count());
        $session = ReviewStationSession::where('student_id', $student->id)->first();
        $this->assertSame($unit->id, $session->unit_id);
        $this->assertSame('pending', $session->status);

        $reviewQuestionIds = ReviewStationQuestion::where('session_id', $session->id)->pluck('question_id')->sort()->values();
        $this->assertSame(3, $reviewQuestionIds->count());
        $this->assertSame($wrongQuestionIds->toArray(), $reviewQuestionIds->toArray());

        $student->refresh();
        $balanceAfterLesson1 = $student->points_balance;
        $this->assertSame(7, $balanceAfterLesson1);

        // الإجابة على أسئلة المراجعة الثلاثة كلها بشكل صحيح
        foreach ($reviewQuestionIds as $qid) {
            $this->withToken($token)
                ->postJson("/api/review-sessions/{$session->id}/answer", [
                    'question_id' => $qid,
                    'selected_answer' => ['selected_option_id' => 'a'],
                ])
                ->assertStatus(201)
                ->assertJsonPath('is_correct', true);
        }

        $session->refresh();
        // نصف نقاط الثلاثة أسئلة فقط (3 × 1 نقطة / 2 = 1 floor)، وليس نصف نقاط الدرس (7) كاملة
        $this->assertSame('completed', $session->status);
        $this->assertSame(1, $session->points_earned);

        $reviewTransaction = PointsTransaction::where('student_id', $student->id)->where('type', 'review_station')->first();
        $this->assertNotNull($reviewTransaction);
        $this->assertSame(1, $reviewTransaction->points_change);

        $student->refresh();
        $this->assertSame(8, $student->points_balance); // 7 + 1 مكافأة المراجعة

        // إكمال الدرس الثاني (الأخير بالوحدة) -> الوحدة أصبحت مكتملة -> منح الهدية
        $attempt2 = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson2->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $q2 = $lesson2->questions()->first();
        $this->withToken($token)
            ->postJson("/api/attempts/{$attempt2['id']}/answer", [
                'question_id' => $q2->id,
                'selected_answer' => ['selected_option_id' => 'a'],
            ])
            ->assertStatus(201);

        $this->withToken($token)
            ->postJson("/api/attempts/{$attempt2['id']}/complete")
            ->assertStatus(200);

        $this->assertSame(1, StudentGiftLog::where('student_id', $student->id)->where('unit_id', $unit->id)->count());

        $student->refresh();
        $this->assertSame(19, $student->points_balance); // 7 + 1 + 1(لعب الدرس الثاني) + 10(الهدية)

        $giftTransaction = PointsTransaction::where('student_id', $student->id)->where('type', 'gift')->first();
        $this->assertNotNull($giftTransaction);
        $this->assertSame(10, $giftTransaction->points_change);
    }
}
