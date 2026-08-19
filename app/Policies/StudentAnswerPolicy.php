<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\StudentAnswer;
use App\Policies\Concerns\ChecksStudentOwnership;

class StudentAnswerPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, StudentAnswer $studentAnswer): bool
    {
        return $this->ownsStudentViaAttempt($user, $studentAnswer);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, StudentAnswer $studentAnswer): bool
    {
        return $this->ownsStudentViaAttempt($user, $studentAnswer);
    }

    public function delete($user, StudentAnswer $studentAnswer): bool
    {
        return $this->ownsStudentViaAttempt($user, $studentAnswer);
    }

    /**
     * student_answers ليس فيها عمود student_id مباشر، بل عبر attempt_id -> student_lesson_attempts.student_id
     */
    private function ownsStudentViaAttempt($user, StudentAnswer $studentAnswer): bool
    {
        $studentId = $studentAnswer->attempt()->value('student_id');

        return $this->ownsStudent($user, $studentId);
    }
}
