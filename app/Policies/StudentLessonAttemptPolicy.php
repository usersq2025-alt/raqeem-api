<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\StudentLessonAttempt;
use App\Policies\Concerns\ChecksStudentOwnership;

class StudentLessonAttemptPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, StudentLessonAttempt $studentLessonAttempt): bool
    {
        return $this->ownsStudent($user, $studentLessonAttempt->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, StudentLessonAttempt $studentLessonAttempt): bool
    {
        return $this->ownsStudent($user, $studentLessonAttempt->student_id);
    }

    public function delete($user, StudentLessonAttempt $studentLessonAttempt): bool
    {
        return $this->ownsStudent($user, $studentLessonAttempt->student_id);
    }
}
