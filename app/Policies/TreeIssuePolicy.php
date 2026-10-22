<?php

namespace App\Policies;

use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TreeIssuePolicy extends ManagerOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCitizen() || $user->isFieldStaff();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof TreeIssue) {
            return false;
        }

        return ($user->isCitizen() || $user->isFieldStaff())
            && (int) $model->reporter_id === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isCitizen() || $user->isFieldStaff();
    }
}
