<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Tree;
use App\Models\User;

class TreePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->role === UserRole::FieldStaff;
    }

    public function view(User $user, Tree $tree): bool
    {
        return $user->isManager() || $user->role === UserRole::FieldStaff;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::FieldStaff;
    }

    public function update(User $user, Tree $tree): bool
    {
        return false;
    }

    public function delete(User $user, Tree $tree): bool
    {
        return false;
    }
}
