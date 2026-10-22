<?php

namespace App\Policies;

use App\Models\AdoptionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdoptionRequestPolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return ['review', 'update'];
    }

    public function viewAny(User $user): bool
    {
        return $user->isCitizen();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof AdoptionRequest) {
            return false;
        }

        return $user->isCitizen()
            && (int) $model->requester_id === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isCitizen();
    }

    public function review(User $user, AdoptionRequest $request): bool
    {
        return $user->isManager()
            && (int) $request->requester_id !== (int) $user->getKey();
    }

    public function update(User $user, Model $model): bool
    {
        return $model instanceof AdoptionRequest
            && $user->isManager()
            && (int) $model->requester_id !== (int) $user->getKey();
    }
}
