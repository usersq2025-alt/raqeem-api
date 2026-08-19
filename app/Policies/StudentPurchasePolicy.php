<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\StudentPurchase;
use App\Policies\Concerns\ChecksStudentOwnership;

class StudentPurchasePolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, StudentPurchase $studentPurchase): bool
    {
        return $this->ownsStudent($user, $studentPurchase->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, StudentPurchase $studentPurchase): bool
    {
        return $this->ownsStudent($user, $studentPurchase->student_id);
    }

    public function delete($user, StudentPurchase $studentPurchase): bool
    {
        return $this->ownsStudent($user, $studentPurchase->student_id);
    }
}
