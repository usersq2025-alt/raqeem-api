<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonAttempt;

class UnitCompletionChecker
{
    /**
     * تعريف "الوحدة مكتملة" المعتمَد بالمرحلة 3 (StorePurchaseService) — يُعاد
     * استخدامه هنا حرفيًا: كل دروس الوحدة لهذا الطالب بحالة completed.
     */
    public function hasCompletedUnit(Student $student, int $unitId): bool
    {
        $lessonIds = Lesson::where('unit_id', $unitId)->pluck('id');

        if ($lessonIds->isEmpty()) {
            return false;
        }

        $completedCount = StudentLessonAttempt::where('student_id', $student->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->distinct('lesson_id')
            ->count('lesson_id');

        return $completedCount >= $lessonIds->count();
    }
}
