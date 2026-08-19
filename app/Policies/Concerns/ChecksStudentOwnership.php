<?php

namespace App\Policies\Concerns;

use App\Models\ParentUser;
use App\Models\Student;

trait ChecksStudentOwnership
{
    /**
     * صحيح فقط إذا كان $user هو ParentUser وله طفل بالفعل بمعرّف $studentId.
     * $user غير مُقيَّد بنوع Authenticatable لأن ParentUser/AdminUser موديلات
     * Eloquent عادية (تستخدم Sanctum HasApiTokens) ولا تُنفِّذ الواجهة تلك.
     */
    protected function ownsStudent($user, ?int $studentId): bool
    {
        if (! $user instanceof ParentUser || ! $studentId) {
            return false;
        }

        return Student::where('id', $studentId)->where('parent_id', $user->id)->exists();
    }
}
