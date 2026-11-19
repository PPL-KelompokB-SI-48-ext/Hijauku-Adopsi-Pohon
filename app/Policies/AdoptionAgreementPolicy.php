<?php

namespace App\Policies;

use App\Models\AdoptionAgreement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdoptionAgreementPolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['accept', 'terminate'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isCitizen();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof AdoptionAgreement) {
            return false;
        }

        return $user->isCitizen()
            && (int) $model->adopter_id === (int) $user->getKey();
    }

    public function accept(User $user, AdoptionAgreement $agreement): bool
    {
        return $user->is_active
            && $user->isCitizen()
            && (int) $agreement->adopter_id === (int) $user->getKey()
            && $agreement->status === 'awaiting_acceptance';
    }

    public function terminate(User $user, AdoptionAgreement $agreement): bool
    {
        return $user->is_active
            && $user->isManager()
            && $agreement->status === 'active';
    }
}
