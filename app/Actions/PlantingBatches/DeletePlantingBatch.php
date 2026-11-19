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
     * Menghapus kelompok penanaman yang belum berisi pohon dan mencatat riwayat audit.
     */
    public function handle(User $actor, PlantingBatch $batch, string $reason): void
    {
        Gate::forUser($actor)->authorize('delete', $batch);

        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penghapusan kelompok penanaman wajib diisi minimal 3 karakter.',
            ]);
        }

        DB::transaction(function () use ($actor, $batch, $reason): void {
            $lockedBatch = PlantingBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->getKey());

            if ($lockedBatch->trees()->exists()) {
                throw ValidationException::withMessages([
                    'planting_batch' => 'Kelompok penanaman tidak dapat dihapus karena masih memiliki pohon terhubung.',
                ]);
            }

            $metadata = [
                'code' => $lockedBatch->code,
                'plot_id' => $lockedBatch->plot_id,
                'planted_on' => $lockedBatch->planted_on?->toDateString(),
                'reason' => $reason,
            ];

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

