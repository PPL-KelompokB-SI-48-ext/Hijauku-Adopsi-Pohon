<?php

namespace App\Policies;

use App\Models\GrowthObservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GrowthObservationPolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['verify', 'update', 'create'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isFieldStaff() || $user->isManager();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof GrowthObservation) {
            return false;
        }

        if ($user->isFieldStaff()) {
            return (int) $model->observed_by === (int) $user->getKey();
        }

        return $user->isManager();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isFieldStaff();
    }

    public function downloadPhoto(User $user, GrowthObservation $observation): bool
    {
        if ($user->isFieldStaff()) {
            return (int) $observation->observed_by === (int) $user->getKey();
        }

        return $user->isManager();
    }

    public function verify(User $user, GrowthObservation $observation): bool
    {
        return $user->isManager()
            && (int) $observation->observed_by !== (int) $user->getKey();
    }

    public function update(User $user, Model $model): bool
    {
        return $model instanceof GrowthObservation
            && $user->isManager()
            && (int) $model->observed_by !== (int) $user->getKey();
    }
}
