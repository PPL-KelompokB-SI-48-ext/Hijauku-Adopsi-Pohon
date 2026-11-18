<?php

namespace App\Actions\PlantingBatches;

use App\Models\PlantingBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeletePlantingBatch
{
    /**
     * Hapus kelompok penanaman kosong dan catat jejak audit.
     */
    public function handle(User $actor, PlantingBatch $batch, ?string $reason = null): void
    {
        Gate::forUser($actor)->authorize('delete', $batch);

        if ($reason !== null) {
            $reason = trim($reason);
            if ($reason === '') {
                $reason = null;
            }
        }

        DB::transaction(function () use ($actor, $batch, $reason): void {
            $lockedBatch = PlantingBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->getKey());

            if ($lockedBatch->trees()->exists()) {
                throw ValidationException::withMessages([
                    'planting_batch' => 'Kelompok penanaman tidak dapat dihapus karena masih berisi pohon. Lepaskan atau pindahkan pohon terlebih dahulu.',
                ]);
            }

            $metadata = [
                'code' => $lockedBatch->code,
                'plot_id' => $lockedBatch->plot_id,
                'planted_on' => $lockedBatch->planted_on?->toDateString(),
            ];

            if ($reason !== null) {
                $metadata['reason'] = $reason;
            }

            $lockedBatch->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'planting_batch.deleted',
                'from_status' => null,
                'to_status' => null,
                'metadata' => $metadata,
            ]);

            $lockedBatch->delete();
        });
    }
}
