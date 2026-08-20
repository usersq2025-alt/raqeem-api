<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\DailyActivityLog;
use App\Models\Student;
use App\Models\StudentBadge;
use Carbon\Carbon;

class StreakService
{
    // B9 — يُستدعى من نفس معاملة LessonAttemptService::complete() الذرّية، وليس كخطوة منفصلة
    public function recordActivityAndUpdateStreak(Student $student): void
    {
        $timezone = config('streak_rules.timezone');
        $today = Carbon::now($timezone)->toDateString();

        $alreadyLoggedToday = DailyActivityLog::where('student_id', $student->id)
            ->whereDate('activity_date', $today)
            ->exists();

        if ($alreadyLoggedToday) {
            // نشاط ثانٍ بنفس اليوم -> لا تغيير بالستريك
            return;
        }

        DailyActivityLog::create([
            'student_id' => $student->id,
            'activity_date' => $today,
        ]);

        $yesterday = Carbon::parse($today, $timezone)->subDay()->toDateString();

        // daily_activity_log هو مصدر الحقيقة (كما يوثّق تعليق schema على students.streak_current)،
        // لا نعتمد على students.last_activity_date كمرجع للمقارنة، فقط كـ cache نُحدِّثه لاحقًا
        $wasActiveYesterday = DailyActivityLog::where('student_id', $student->id)
            ->whereDate('activity_date', $yesterday)
            ->exists();

        $newStreak = $wasActiveYesterday ? $student->streak_current + 1 : 1;

        $student->streak_current = $newStreak;
        $student->streak_longest = max($student->streak_longest, $newStreak); // لا ينخفض أبدًا
        $student->last_activity_date = $today;
        $student->save();

        $this->awardEligibleBadges($student, $newStreak);
    }

    private function awardEligibleBadges(Student $student, int $streak): void
    {
        foreach (config('streak_rules.badges') as $badgeConfig) {
            if ($streak < $badgeConfig['threshold_value']) {
                continue;
            }

            $badge = Badge::firstOrCreate(
                ['code' => $badgeConfig['code']],
                [
                    'name_ar' => $badgeConfig['name_ar'],
                    'name_en' => $badgeConfig['name_en'],
                    'threshold_type' => 'streak_days',
                    'threshold_value' => $badgeConfig['threshold_value'],
                ]
            );

            $alreadyAwarded = StudentBadge::where('student_id', $student->id)
                ->where('badge_id', $badge->id)
                ->exists();

            if (! $alreadyAwarded) {
                StudentBadge::create([
                    'student_id' => $student->id,
                    'badge_id' => $badge->id,
                    'earned_at' => now(),
                ]);
            }
        }
    }
}
