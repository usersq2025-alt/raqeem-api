<?php

namespace App\Policies;

use App\Models\DailyActivityLog;
use App\Models\ParentUser;
use App\Policies\Concerns\ChecksStudentOwnership;

class DailyActivityLogPolicy
{
    use ChecksStudentOwnership;

    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, DailyActivityLog $dailyActivityLog): bool
    {
        return $this->ownsStudent($user, $dailyActivityLog->student_id);
    }

    public function create($user, ?int $studentId): bool
    {
        return $this->ownsStudent($user, $studentId);
    }

    public function update($user, DailyActivityLog $dailyActivityLog): bool
    {
        return $this->ownsStudent($user, $dailyActivityLog->student_id);
    }

    public function delete($user, DailyActivityLog $dailyActivityLog): bool
    {
        return $this->ownsStudent($user, $dailyActivityLog->student_id);
    }
}
