<?php

namespace App\Policies;

use App\Models\GrowthObservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GrowthObservationPolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['verify', 'update'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isFieldStaff();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof GrowthObservation) {
            return false;
        }

        return $user->isFieldStaff()
            && (int) $model->observed_by === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isFieldStaff();
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
