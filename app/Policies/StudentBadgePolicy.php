<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\StudentBadge;
use App\Policies\Concerns\ChecksStudentOwnership;

class StudentBadgePolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, StudentBadge $studentBadge): bool
    {
        return $this->ownsStudent($user, $studentBadge->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, StudentBadge $studentBadge): bool
    {
        return $this->ownsStudent($user, $studentBadge->student_id);
    }

    public function delete($user, StudentBadge $studentBadge): bool
    {
        return $this->ownsStudent($user, $studentBadge->student_id);
    }
}
