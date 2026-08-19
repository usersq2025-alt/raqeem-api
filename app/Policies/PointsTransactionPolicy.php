<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\PointsTransaction;
use App\Policies\Concerns\ChecksStudentOwnership;

class PointsTransactionPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, PointsTransaction $pointsTransaction): bool
    {
        return $this->ownsStudent($user, $pointsTransaction->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, PointsTransaction $pointsTransaction): bool
    {
        return $this->ownsStudent($user, $pointsTransaction->student_id);
    }

    public function delete($user, PointsTransaction $pointsTransaction): bool
    {
        return $this->ownsStudent($user, $pointsTransaction->student_id);
    }
}
