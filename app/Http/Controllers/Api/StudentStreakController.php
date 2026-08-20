<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;

class StudentStreakController extends Controller
{
    // GET /api/students/{student}/streak — قراءة فقط
    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $badges = $student->studentBadges()
            ->with('badge')
            ->orderBy('earned_at')
            ->get()
            ->map(fn ($sb) => [
                'code' => $sb->badge->code,
                'name_ar' => $sb->badge->name_ar,
                'name_en' => $sb->badge->name_en,
                'earned_at' => $sb->earned_at?->toIso8601String(),
            ]);

        return response()->json([
            'student_id' => $student->id,
            'streak_current' => $student->streak_current,
            'streak_longest' => $student->streak_longest,
            'last_activity_date' => $student->last_activity_date?->toDateString(),
            'badges' => $badges,
        ]);
    }
}
