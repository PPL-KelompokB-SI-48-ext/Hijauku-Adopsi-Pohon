<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PlantingBatch;
use App\Models\User;

class PlantingBatchPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->role === UserRole::FieldStaff;
    }

    public function view(User $user, PlantingBatch $batch): bool
    {
        return $user->isManager() || $user->role === UserRole::FieldStaff;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::FieldStaff;
    }

    public function update(User $user, PlantingBatch $batch): bool
    {
        return false;
    }

    public function delete(User $user, PlantingBatch $batch): bool
    {
        return false;
    }
}

