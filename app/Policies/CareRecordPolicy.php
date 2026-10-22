<?php

namespace App\Policies;

use App\Models\CareRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CareRecordPolicy extends ManagerOnlyPolicy
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
        if (! $model instanceof CareRecord) {
            return false;
        }

        return $user->isFieldStaff()
            && (int) $model->performed_by === (int) $user->getKey();
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return $user->isFieldStaff();
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
