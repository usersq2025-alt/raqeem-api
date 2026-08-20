<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\ReviewStationQuestion;
use App\Policies\Concerns\ChecksStudentOwnership;

class ReviewStationQuestionPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, ReviewStationQuestion $reviewStationQuestion): bool
    {
        return $this->ownsStudentViaSession($user, $reviewStationQuestion);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, ReviewStationQuestion $reviewStationQuestion): bool
    {
        return $this->ownsStudentViaSession($user, $reviewStationQuestion);
    }

    public function delete($user, ReviewStationQuestion $reviewStationQuestion): bool
    {
        return $this->ownsStudentViaSession($user, $reviewStationQuestion);
    }

    /**
     * review_station_questions ليس فيها عمود student_id مباشر، بل عبر
     * session_id -> review_station_sessions.student_id
     */
    private function ownsStudentViaSession($user, ReviewStationQuestion $reviewStationQuestion): bool
    {
        $studentId = $reviewStationQuestion->session()->value('student_id');

        return $this->ownsStudent($user, $studentId);
    }
}
