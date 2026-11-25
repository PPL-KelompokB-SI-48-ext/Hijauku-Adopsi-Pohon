<?php

namespace App\Policies;

use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TreeIssuePolicy extends ManagerOnlyPolicy
{
    protected function managerRestrictedAbilities(): array
    {
        return [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'deleteAny',
            'downloadEvidence',
            'handle',
            'reject',
        ];
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isCitizen() || $user->isFieldStaff();
    }

    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof TreeIssue) {
            return false;
        }

        return $user->isManager()
            || (($user->isCitizen() || $user->isFieldStaff())
                && (int) $model->reporter_id === (int) $user->getKey());
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isCitizen() || $user->isFieldStaff();
    }

    public function downloadEvidence(User $user, TreeIssue $issue): bool
    {
        return $issue->evidence_path !== null
            && ($user->isManager()
                || (($user->isCitizen() || $user->isFieldStaff())
                    && (int) $issue->reporter_id === (int) $user->getKey()));
    }

    public function handle(User $user, TreeIssue $issue): bool
    {
        return $user->isManager();
    }

    public function reject(User $user, TreeIssue $issue): bool
    {
        return $user->isManager();
    }
}
