<?php

namespace App\Policies;

use App\Models\ActivityRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityRegistrationPolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['create', 'cancel', 'withdraw', 'delete', 'restore', 'forceDelete'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isCitizen() || $user->isManager();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof ActivityRegistration) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $user->isCitizen()
            && (int) $model->user_id === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isCitizen();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->isManager();
    }

    public function recordAttendance(User $user, ActivityRegistration $registration): bool
    {
        return $user->isManager();
    }

    public function correctAttendance(User $user, ActivityRegistration $registration): bool
    {
        return $user->isManager();
    }

    public function cancel(User $user, ActivityRegistration $registration): bool
    {
        return $user->isCitizen() && (int) $registration->user_id === (int) $user->getKey();
    }

    public function withdraw(User $user, ActivityRegistration $registration): bool
    {
        return $this->cancel($user, $registration);
    }
}
