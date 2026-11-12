<?php

namespace App\Policies;

use App\Models\CareRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CareRecordPolicy extends ManagerOnlyPolicy
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
        if (! $model instanceof CareRecord) {
            return false;
        }

        // Field staff: only their own records
        if ($user->isFieldStaff()) {
            return (int) $model->performed_by === (int) $user->getKey();
        }

        // Manager: can view all records
        return $user->isManager();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isFieldStaff();
    }

    public function downloadPhoto(User $user, CareRecord $record): bool
    {
        // Field staff: only their own record's photo
        if ($user->isFieldStaff()) {
            return (int) $record->performed_by === (int) $user->getKey();
        }

        // Manager can view all photos
        return $user->isManager();
    }

    public function verify(User $user, CareRecord $record): bool
    {
        return $user->isManager()
            && (int) $record->performed_by !== (int) $user->getKey();
    }

    public function update(User $user, Model $model): bool
    {
        return $model instanceof CareRecord
            && $user->isManager()
            && (int) $model->performed_by !== (int) $user->getKey();
    }
}
