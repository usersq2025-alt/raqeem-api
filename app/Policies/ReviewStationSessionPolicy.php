<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\ReviewStationSession;
use App\Policies\Concerns\ChecksStudentOwnership;

class ReviewStationSessionPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, ReviewStationSession $reviewStationSession): bool
    {
        return $this->ownsStudent($user, $reviewStationSession->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, ReviewStationSession $reviewStationSession): bool
    {
        return $this->ownsStudent($user, $reviewStationSession->student_id);
    }

    public function delete($user, ReviewStationSession $reviewStationSession): bool
    {
        return $this->ownsStudent($user, $reviewStationSession->student_id);
    }
}
