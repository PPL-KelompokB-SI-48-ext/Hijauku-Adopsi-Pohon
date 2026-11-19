<?php

namespace App\Actions\TreeSpecies;

use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeactivateTreeSpecies
{
    /**
     * Deactivate a tree species and record its status transition audit.
     */
    public function handle(User $actor, TreeSpecies $species, string $reason): TreeSpecies
    {
        Gate::forUser($actor)->authorize('update', $species);

        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penonaktifan jenis pohon wajib diisi minimal 3 karakter.',
            ]);
        }

        return DB::transaction(function () use ($actor, $species, $reason): TreeSpecies {
            $lockedSpecies = TreeSpecies::query()
                ->lockForUpdate()
                ->findOrFail($species->getKey());

            $wasActive = (bool) $lockedSpecies->is_active;

            $lockedSpecies->is_active = false;
            $lockedSpecies->save();

            $metadata = [
                'reason' => $reason,
            ];

            $lockedSpecies->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'tree_species.deactivated',
                'from_status' => $wasActive ? 'active' : 'inactive',
                'to_status' => 'inactive',
                'metadata' => $metadata,
            ]);

            return $lockedSpecies;
        });
    }
}

