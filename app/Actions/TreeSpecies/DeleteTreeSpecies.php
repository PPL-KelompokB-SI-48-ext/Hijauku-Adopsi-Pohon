<?php

namespace App\Actions\TreeSpecies;

use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeleteTreeSpecies
{
    /**
     * Delete an unused tree species and record its audit log.
     */
    public function handle(User $actor, TreeSpecies $species, ?string $reason = null): void
    {
        Gate::forUser($actor)->authorize('delete', $species);

        if ($reason !== null) {
            $reason = trim($reason);
            if ($reason === '') {
                $reason = null;
            }
        }

        DB::transaction(function () use ($actor, $species, $reason): void {
            $lockedSpecies = TreeSpecies::query()
                ->lockForUpdate()
                ->findOrFail($species->getKey());

            if ($lockedSpecies->trees()->exists()) {
                throw ValidationException::withMessages([
                    'tree_species' => 'Jenis pohon tidak dapat dihapus karena sudah digunakan oleh inventaris pohon. Silakan nonaktifkan jenis pohon ini.',
                ]);
            }

            $metadata = [
                'name' => $lockedSpecies->name,
                'care_notes' => $lockedSpecies->care_notes,
                'is_active' => (bool) $lockedSpecies->is_active,
            ];

            if ($reason !== null) {
                $metadata['reason'] = $reason;
            }

            $lockedSpecies->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'tree_species.deleted',
                'from_status' => $lockedSpecies->is_active ? 'active' : 'inactive',
                'to_status' => null,
                'metadata' => $metadata,
            ]);

            $lockedSpecies->delete();
        });
    }
}

