<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\Student;

class StudentPolicy
{
    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, Student $student): bool
    {
        return $user instanceof ParentUser && $user->id === $student->parent_id;
    }

    public function create($user): bool
    {
        // كل ولي أمر مسموح له بإضافة طفل جديد لحسابه هو (parent_id يُفرض بالـ Controller، لا يُقرأ من الطلب)
        return $user instanceof ParentUser;
    }

    public function update($user, Student $student): bool
    {
        return $user instanceof ParentUser && $user->id === $student->parent_id;
    }

    public function delete($user, Student $student): bool
    {
        return $user instanceof ParentUser && $user->id === $student->parent_id;
    }
}
