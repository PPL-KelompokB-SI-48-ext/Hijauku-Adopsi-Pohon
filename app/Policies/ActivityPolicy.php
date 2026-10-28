<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityPolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['update', 'delete', 'restore', 'forceDelete'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Model $model): bool
    {
        return $model instanceof Activity && $user->isManager();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }
}

