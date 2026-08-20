<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameType;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\ParentUser;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentLessonAttempt;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LessonGameplayTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithToken(): array
    {
        $grade = Grade::create(['level' => 1, 'name_ar' => 'الأول', 'name_en' => 'Grade 1']);
        $parent = ParentUser::create([
            'public_id' => 'RQMP-000001',
            'full_name' => 'ولي أمر',
            'email' => 'parent@example.com',
            'password_hash' => Hash::make('Password123'),
            'status' => 'active',
        ]);
        $student = Student::create([
            'public_id' => 'RQMS-000001',
            'parent_id' => $parent->id,
            'full_name' => 'طالب',
            'birth_date' => '2016-01-01',
            'grade_id' => $grade->id,
            'gender' => 'male',
        ]);
        $token = $parent->createToken('t')->plainTextToken;

        return [$student, $token, $grade];
    }

    private function makeLessonWithQuestions(int $unitId, int $sortOrder, int $questionCount, string $gameTypeCode = 'mcq'): Lesson
    {
        $gameType = GameType::firstOrCreate(
            ['code' => $gameTypeCode],
            ['name_ar' => $gameTypeCode, 'name_en' => $gameTypeCode, 'is_active' => true]
        );

        $lesson = Lesson::create([
            'unit_id' => $unitId,
            'title' => "درس ترتيبه {$sortOrder}",
            'sort_order' => $sortOrder,
            'status' => 'published',
        ]);

        $game = Game::create([
            'lesson_id' => $lesson->id,
            'game_type_id' => $gameType->id,
            'title' => 'لعبة',
            'sort_order' => 1,
            'status' => 'published',
        ]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = Question::create([
                'lesson_id' => $lesson->id,
                'game_type_id' => $gameType->id,
                'question_text' => "سؤال {$i}",
                'difficulty' => 'medium',
                'payload' => ['options' => [['id' => 'a', 'text' => 'A'], ['id' => 'b', 'text' => 'B']], 'correct_option_id' => 'a'],
                'status' => 'published',
                'source' => 'manual',
            ]);

            GameQuestion::create([
                'game_id' => $game->id,
                'question_id' => $question->id,
                'sort_order' => $i,
            ]);
        }

        return $lesson;
    }

    public function test_sequential_lock_blocks_second_lesson_until_first_completed(): void
    {
        [$student, $token] = $this->makeStudentWithToken();
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $grade = Grade::first();
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);

        $lesson1 = $this->makeLessonWithQuestions($unit->id, 1, 3);
        $lesson2 = $this->makeLessonWithQuestions($unit->id, 2, 3);

        // محاولة بدء الدرس الثاني قبل إتمام الأول -> رفض
        $this->withToken($token)
            ->postJson("/api/lessons/{$lesson2->id}/attempts/start", ['student_id' => $student->id])
            ->assertStatus(422);

        // إتمام الدرس الأول بالكامل بإجابات صحيحة
        $start = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson1->id}/attempts/start", ['student_id' => $student->id])
            ->assertStatus(200)
            ->json();

        $attemptId = $start['id'];

        foreach ($lesson1->questions as $q) {
            $this->withToken($token)
                ->postJson("/api/attempts/{$attemptId}/answer", [
                    'question_id' => $q->id,
                    'selected_answer' => ['selected_option_id' => 'a'],
                ])
                ->assertStatus(201);
        }

        $this->withToken($token)
            ->postJson("/api/attempts/{$attemptId}/complete")
            ->assertStatus(200)
            ->assertJsonPath('status', 'completed');

        // الآن الدرس الثاني مسموح
        $this->withToken($token)
            ->postJson("/api/lessons/{$lesson2->id}/attempts/start", ['student_id' => $student->id])
            ->assertStatus(200)
            ->assertJsonPath('status', 'in_progress');
    }

    public function test_resume_returns_same_in_progress_attempt(): void
    {
        [$student, $token] = $this->makeStudentWithToken();
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $grade = Grade::first();
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);
        $lesson = $this->makeLessonWithQuestions($unit->id, 1, 5);

        $first = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $second = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $this->assertSame($first['id'], $second['id']);
        $this->assertSame(1, StudentLessonAttempt::count());
    }

    public function test_battery_depletes_at_fifty_percent_wrong_and_locks_recharge(): void
    {
        [$student, $token] = $this->makeStudentWithToken();
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $grade = Grade::first();
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);
        $lesson = $this->makeLessonWithQuestions($unit->id, 1, 20); // 20 سؤال: 50% = 10 أخطاء بالضبط

        $attempt = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $questions = $lesson->questions()->orderBy('id')->get();

        for ($i = 0; $i < 10; $i++) {
            $resp = $this->withToken($token)
                ->postJson("/api/attempts/{$attempt['id']}/answer", [
                    'question_id' => $questions[$i]->id,
                    'selected_answer' => ['selected_option_id' => 'b'], // خطأ دائمًا
                ]);

            if ($i < 9) {
                $resp->assertStatus(201)->assertJsonPath('is_correct', false);
            }
        }

        // الإجابة العاشرة الخاطئة (wrong_count=10 من 20 = 50%) يجب أن تُفرِغ البطارية
        $resp->assertStatus(201);
        $this->assertSame('battery_depleted', $resp->json('attempt.status'));
        $this->assertSame(10, $resp->json('attempt.wrong_count'));
        $this->assertSame(0, $resp->json('attempt.battery_segments_remaining'));

        // أي محاولة إجابة تالية تُرفض بـ 423 لحين انتهاء الشحن
        $this->withToken($token)
            ->postJson("/api/attempts/{$attempt['id']}/answer", [
                'question_id' => $questions[10]->id,
                'selected_answer' => ['selected_option_id' => 'a'],
            ])
            ->assertStatus(423);

        $fresh = StudentLessonAttempt::find($attempt['id']);
        $this->assertSame('battery_depleted', $fresh->status);
        $this->assertNotNull($fresh->recharge_ends_at);
        $this->assertEqualsWithDelta(15, now()->diffInMinutes($fresh->recharge_ends_at), 0.1);
    }

    public function test_completion_scores_points_and_stars_correctly_and_replay_gives_zero_points(): void
    {
        [$student, $token] = $this->makeStudentWithToken();
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $grade = Grade::first();
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);
        $lesson = $this->makeLessonWithQuestions($unit->id, 1, 10);

        $attempt = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $questions = $lesson->questions()->orderBy('id')->get();

        // 8 صحيحة + 2 خاطئة = 80% -> نجمتان، 8 نقاط
        foreach ($questions as $i => $q) {
            $wrong = $i >= 8;
            $this->withToken($token)
                ->postJson("/api/attempts/{$attempt['id']}/answer", [
                    'question_id' => $q->id,
                    'selected_answer' => ['selected_option_id' => $wrong ? 'b' : 'a'],
                ])
                ->assertStatus(201);
        }

        $complete = $this->withToken($token)
            ->postJson("/api/attempts/{$attempt['id']}/complete")
            ->assertStatus(200)
            ->json();

        $this->assertSame('completed', $complete['status']);
        $this->assertSame(2, $complete['stars']);
        $this->assertSame(8, $complete['points_earned']);

        $student->refresh();
        $this->assertSame(8, $student->points_balance);
        $this->assertSame(1, \App\Models\PointsTransaction::where('student_id', $student->id)->count());

        // إعادة لعب: تُنشئ محاولة ثانية attempt_number=2 بنفس النقاط الكاملة -> نقاط=0، لكن النجوم لا تنخفض
        $replay = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $this->assertSame(2, $replay['attempt_number']);

        foreach ($questions as $q) {
            $this->withToken($token)
                ->postJson("/api/attempts/{$replay['id']}/answer", [
                    'question_id' => $q->id,
                    'selected_answer' => ['selected_option_id' => 'a'],
                ])
                ->assertStatus(201);
        }

        $replayComplete = $this->withToken($token)
            ->postJson("/api/attempts/{$replay['id']}/complete")
            ->assertStatus(200)
            ->json();

        $this->assertSame(0, $replayComplete['points_earned']);
        $this->assertSame(3, $replayComplete['stars']); // 100% بإعادة اللعب لكن الأعلى من السابق أيضًا (2) فتُحدَّث للأعلى=3

        $student->refresh();
        $this->assertSame(8, $student->points_balance); // لم يتغيّر، إعادة اللعب بلا نقاط
        $this->assertSame(1, \App\Models\PointsTransaction::where('student_id', $student->id)->count()); // سجل واحد فقط
    }

    public function test_direct_write_routes_on_attempts_and_answers_are_closed(): void
    {
        [$student, $token] = $this->makeStudentWithToken();
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $grade = Grade::first();
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);
        $lesson = $this->makeLessonWithQuestions($unit->id, 1, 3);

        $attempt = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        // محاولة PATCH مباشر لإكمال المحاولة دون إجابة أي سؤال -> يجب رفضها الآن (405)
        $this->withToken($token)
            ->patchJson("/api/student-lesson-attempts/{$attempt['id']}", ['status' => 'completed'])
            ->assertStatus(405);

        $this->withToken($token)
            ->postJson('/api/student-lesson-attempts', ['student_id' => $student->id, 'lesson_id' => $lesson->id, 'status' => 'completed'])
            ->assertStatus(405);

        $this->withToken($token)
            ->deleteJson("/api/student-lesson-attempts/{$attempt['id']}")
            ->assertStatus(405);

        $this->withToken($token)
            ->postJson('/api/student-answers', ['attempt_id' => $attempt['id'], 'question_id' => 1, 'is_correct' => true])
            ->assertStatus(405);

        $this->withToken($token)
            ->patchJson('/api/student-answers/1', ['is_correct' => true])
            ->assertStatus(405);

        // تأكيد أن حالة المحاولة لم تتأثر فعليًا بأي من هذه المحاولات
        $fresh = StudentLessonAttempt::find($attempt['id']);
        $this->assertSame('in_progress', $fresh->status);
        $this->assertSame(0, $fresh->correct_count);
    }

    public function test_last_remaining_question_at_exact_depletion_ratio_still_allows_completion(): void
    {
        [$student, $token] = $this->makeStudentWithToken();
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $grade = Grade::first();
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);
        $lesson = $this->makeLessonWithQuestions($unit->id, 1, 10); // 10 أسئلة، عتبة الإفراغ 50% = 5 أخطاء بالضبط

        $attempt = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $student->id])
            ->json();

        $questions = $lesson->questions()->orderBy('id')->get();

        // 5 إجابات صحيحة أولًا
        for ($i = 0; $i < 5; $i++) {
            $this->withToken($token)
                ->postJson("/api/attempts/{$attempt['id']}/answer", [
                    'question_id' => $questions[$i]->id,
                    'selected_answer' => ['selected_option_id' => 'a'],
                ])
                ->assertStatus(201);
        }

        // 4 إجابات خاطئة تالية (wrong_count=4 من 10 = 40%، دون العتبة بعد)
        for ($i = 5; $i < 9; $i++) {
            $this->withToken($token)
                ->postJson("/api/attempts/{$attempt['id']}/answer", [
                    'question_id' => $questions[$i]->id,
                    'selected_answer' => ['selected_option_id' => 'b'],
                ])
                ->assertStatus(201)
                ->assertJsonPath('attempt.status', 'in_progress');
        }

        // السؤال العاشر (الأخير المتبقي) خطأ أيضًا -> wrong_count=5 من 10 = 50% بالضبط،
        // لكنه آخر سؤال بالدرس -> يجب ألا تُفرَغ البطارية
        $last = $this->withToken($token)
            ->postJson("/api/attempts/{$attempt['id']}/answer", [
                'question_id' => $questions[9]->id,
                'selected_answer' => ['selected_option_id' => 'b'],
            ])
            ->assertStatus(201)
            ->json();

        $this->assertSame(5, $last['attempt']['wrong_count']);
        $this->assertSame('in_progress', $last['attempt']['status']);
        $this->assertNull($last['attempt']['recharge_ends_at']);

        // استدعاء /complete ينجح مباشرة بلا أي رفض
        $this->withToken($token)
            ->postJson("/api/attempts/{$attempt['id']}/complete")
            ->assertStatus(200)
            ->assertJsonPath('status', 'completed');
    }
}
