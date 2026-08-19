<?php

namespace App\Policies;

use App\Models\ParentUser;

class ParentUserPolicy
{
    public function viewAny($user): bool
    {
        return $user instanceof ParentUser;
    }

    public function view($user, ParentUser $parentUser): bool
    {
        return $user instanceof ParentUser && $user->id === $parentUser->id;
    }

    public function create($user): bool
    {
        // إنشاء حساب ولي أمر جديد يتم فقط عبر POST /register (بدون توكن)، وليس عبر هذا الـ CRUD المصادَق
        return false;
    }

    public function update($user, ParentUser $parentUser): bool
    {
        return $user instanceof ParentUser && $user->id === $parentUser->id;
    }

    public function delete($user, ParentUser $parentUser): bool
    {
        return $user instanceof ParentUser && $user->id === $parentUser->id;
    }
}
