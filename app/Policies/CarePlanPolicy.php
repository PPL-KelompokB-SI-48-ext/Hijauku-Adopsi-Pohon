<?php

namespace App\Policies;

use App\Models\CarePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CarePlanPolicy extends ManagerOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isFieldStaff();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof CarePlan) {
            return false;
        }

        return $user->isFieldStaff()
            && $model->assignment?->assignee_id !== null
            && (int) $model->assignment->assignee_id === (int) $user->getKey();
    }
}
