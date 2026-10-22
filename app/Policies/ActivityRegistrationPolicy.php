<?php

namespace App\Policies;

use App\Models\ActivityRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityRegistrationPolicy extends ManagerOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCitizen();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof ActivityRegistration) {
            return false;
        }

        return $user->isCitizen()
            && (int) $model->user_id === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isCitizen();
    }
}
