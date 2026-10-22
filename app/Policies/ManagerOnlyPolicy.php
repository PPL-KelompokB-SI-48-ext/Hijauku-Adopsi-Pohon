<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Default-deny policy used until a domain explicitly defines safe non-manager access.
 */
class ManagerOnlyPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isManager() && ! in_array($ability, $this->managerRestrictedAbilities(), true)) {
            return true;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function managerRestrictedAbilities(): array
    {
        return [];
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Model $model): bool
    {
        return false;
    }

    public function create(User $user, ?Model $model = null): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }
}
