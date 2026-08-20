<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameType;
use App\Models\Grade;
use App\Models\ParentUser;
use App\Models\Student;
use App\Models\StudentBadge;
use App\Models\Subject;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StreakBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // تنظيف أي وقت مُحاكى حتى لا يتسرّب لاختبارات أخرى
        parent::tearDown();
    }

    private function makeOneQuestionLesson(int $sortOrder): array
    {
        $grade = Grade::firstOrCreate(['level' => 1], ['name_ar' => 'الأول', 'name_en' => 'Grade 1']);
        $subject = Subject::firstOrCreate(['name_ar' => 'مادة'], ['name_en' => 'Subject']);
        $mcq = GameType::firstOrCreate(['code' => 'mcq'], ['name_ar' => 'mcq', 'name_en' => 'mcq', 'is_active' => true]);

        // كل درس بوحدة مستقلة تمامًا لتفادي أي تعارض مع قفل B5 التتابعي (خارج نطاق اختبار الستريك)
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => "وحدة {$sortOrder}", 'sort_order' => $sortOrder, 'status' => 'published']);
        $lesson = \App\Models\Lesson::create(['unit_id' => $unit->id, 'title' => "درس {$sortOrder}", 'sort_order' => 1, 'status' => 'published']);
        $game = Game::create(['lesson_id' => $lesson->id, 'game_type_id' => $mcq->id, 'title' => 'لعبة', 'sort_order' => 1, 'status' => 'published']);
        $question = \App\Models\Question::create([
            'lesson_id' => $lesson->id, 'game_type_id' => $mcq->id, 'question_text' => 'سؤال',
            'difficulty' => 'medium',
            'payload' => ['options' => [['id' => 'a', 'text' => 'A']], 'correct_option_id' => 'a'],
            'status' => 'published', 'source' => 'manual',
        ]);
        GameQuestion::create(['game_id' => $game->id, 'question_id' => $question->id, 'sort_order' => 1]);

        return [$lesson, $question, $grade];
    }

    private function completeLessonViaApi(string $token, int $studentId, $lesson, $question): array
    {
        $start = $this->withToken($token)
            ->postJson("/api/lessons/{$lesson->id}/attempts/start", ['student_id' => $studentId])
            ->json();

        $this->withToken($token)
            ->postJson("/api/attempts/{$start['id']}/answer", [
                'question_id' => $question->id,
                'selected_answer' => ['selected_option_id' => 'a'],
            ])
            ->assertStatus(201);

        return $this->withToken($token)
            ->postJson("/api/attempts/{$start['id']}/complete")
            ->assertStatus(200)
            ->json();
    }

    public function test_three_consecutive_days_build_streak_and_grant_badge_on_day_three(): void
    {
        $parent = ParentUser::create([
            'public_id' => 'RQMP-000001', 'full_name' => 'ولي أمر', 'email' => 'parent@example.com',
            'password_hash' => Hash::make('Password123'), 'status' => 'active',
        ]);

        [$lesson1, $q1, $grade] = $this->makeOneQuestionLesson(1);
        [$lesson2, $q2] = $this->makeOneQuestionLesson(2);
        [$lesson3, $q3] = $this->makeOneQuestionLesson(3);
        [$lesson5, $q5] = $this->makeOneQuestionLesson(5); // لليوم الخامس (بعد تفويت اليوم الرابع)

        $student = Student::create([
            'public_id' => 'RQMS-000001', 'parent_id' => $parent->id, 'full_name' => 'طالب',
            'birth_date' => '2016-01-01', 'grade_id' => $grade->id, 'gender' => 'male',
        ]);

        $token = $parent->createToken('t')->plainTextToken;

        // اليوم 1 (بتوقيت الرياض)
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00', 'Asia/Riyadh'));
        $this->completeLessonViaApi($token, $student->id, $lesson1, $q1);
        $student->refresh();
        $day1Streak = $student->streak_current;
        $day1Longest = $student->streak_longest;

        // اليوم 2
        Carbon::setTestNow(Carbon::parse('2026-01-02 10:00:00', 'Asia/Riyadh'));
        $this->completeLessonViaApi($token, $student->id, $lesson2, $q2);
        $student->refresh();
        $day2Streak = $student->streak_current;

        // اليوم 3 -> يجب منح الشارة الأولى بالضبط هنا
        Carbon::setTestNow(Carbon::parse('2026-01-03 10:00:00', 'Asia/Riyadh'));
        $this->completeLessonViaApi($token, $student->id, $lesson3, $q3);
        $student->refresh();
        $day3Streak = $student->streak_current;
        $day3Longest = $student->streak_longest;
        $badgeCountAfterDay3 = StudentBadge::where('student_id', $student->id)->count();

        $this->assertSame(1, $day1Streak);
        $this->assertSame(1, $day1Longest);
        $this->assertSame(2, $day2Streak);
        $this->assertSame(3, $day3Streak);
        $this->assertSame(3, $day3Longest);
        $this->assertSame(1, $badgeCountAfterDay3);

        // فوّت اليوم 4، ولعب باليوم 5 -> الستريك يرجع لـ1 (لا صفر)، والأعلى يبقى 3
        Carbon::setTestNow(Carbon::parse('2026-01-05 10:00:00', 'Asia/Riyadh'));
        $this->completeLessonViaApi($token, $student->id, $lesson5, $q5);
        $student->refresh();

        $this->assertSame(1, $student->streak_current);
        $this->assertSame(3, $student->streak_longest); // لم يتأثر

        // نفس مسار القراءة الدلالي GET /students/{id}/streak
        $streakResponse = $this->withToken($token)
            ->getJson("/api/students/{$student->id}/streak")
            ->assertStatus(200)
            ->json();

        $this->assertSame(1, $streakResponse['streak_current']);
        $this->assertSame(3, $streakResponse['streak_longest']);
        $this->assertCount(1, $streakResponse['badges']);
        $this->assertSame('streak_3_days', $streakResponse['badges'][0]['code']);
    }

    public function test_second_completion_same_day_does_not_change_streak(): void
    {
        $parent = ParentUser::create([
            'public_id' => 'RQMP-000002', 'full_name' => 'ولي أمر', 'email' => 'parent2@example.com',
            'password_hash' => Hash::make('Password123'), 'status' => 'active',
        ]);

        [$lesson1, $q1, $grade] = $this->makeOneQuestionLesson(1);
        [$lesson2, $q2] = $this->makeOneQuestionLesson(2);

        $student = Student::create([
            'public_id' => 'RQMS-000002', 'parent_id' => $parent->id, 'full_name' => 'طالب',
            'birth_date' => '2016-01-01', 'grade_id' => $grade->id, 'gender' => 'male',
        ]);

        $token = $parent->createToken('t')->plainTextToken;

        Carbon::setTestNow(Carbon::parse('2026-02-01 09:00:00', 'Asia/Riyadh'));
        $this->completeLessonViaApi($token, $student->id, $lesson1, $q1);
        $student->refresh();
        $this->assertSame(1, $student->streak_current);

        // درس ثانٍ بنفس اليوم (وقت مختلف بالساعة، لكن نفس التاريخ بتوقيت الرياض)
        Carbon::setTestNow(Carbon::parse('2026-02-01 21:00:00', 'Asia/Riyadh'));
        $this->completeLessonViaApi($token, $student->id, $lesson2, $q2);
        $student->refresh();

        $this->assertSame(1, $student->streak_current); // لا تغيير
        $this->assertSame(1, \App\Models\DailyActivityLog::where('student_id', $student->id)->count()); // سجل واحد فقط لهذا اليوم
    }
}
