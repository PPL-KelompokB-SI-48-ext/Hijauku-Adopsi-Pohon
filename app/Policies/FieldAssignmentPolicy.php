<?php

namespace App\Policies;

use App\Models\FieldAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FieldAssignmentPolicy extends ManagerOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isFieldStaff();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof FieldAssignment) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $user->isFieldStaff()
            && $model->assignee_id !== null
            && (int) $model->assignee_id === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->isManager();
    }
}
