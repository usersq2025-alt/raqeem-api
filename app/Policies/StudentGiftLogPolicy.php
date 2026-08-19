<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\StudentGiftLog;
use App\Policies\Concerns\ChecksStudentOwnership;

class StudentGiftLogPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, StudentGiftLog $studentGiftLog): bool
    {
        return $this->ownsStudent($user, $studentGiftLog->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, StudentGiftLog $studentGiftLog): bool
    {
        return $this->ownsStudent($user, $studentGiftLog->student_id);
    }

    public function delete($user, StudentGiftLog $studentGiftLog): bool
    {
        return $this->ownsStudent($user, $studentGiftLog->student_id);
    }
}
