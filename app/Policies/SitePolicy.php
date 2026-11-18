<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SitePolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['update', 'delete'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Model $model): bool
    {
        return $model instanceof Site && $user->isManager();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Model $model): bool
    {
        return $model instanceof Site && $user->isManager();
    }

    public function delete(User $user, Model $model): bool
    {
        return $model instanceof Site && $user->isManager();
    }
}
