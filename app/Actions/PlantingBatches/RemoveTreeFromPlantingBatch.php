<?php

namespace App\Actions\PlantingBatches;

use App\Models\PlantingBatch;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RemoveTreeFromPlantingBatch
{
    /**
     * Lepaskan pohon dari kelompok penanaman.
     */
    public function handle(User $actor, PlantingBatch $batch, Tree $tree, ?string $reason = null): void
    {
        Gate::forUser($actor)->authorize('update', $batch);

        if ($reason !== null) {
            $reason = trim($reason);
            if ($reason === '') {
                $reason = null;
            }
        }

        DB::transaction(function () use ($actor, $batch, $tree, $reason): void {
            $lockedBatch = PlantingBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->getKey());

            $lockedTree = Tree::query()
                ->lockForUpdate()
                ->findOrFail($tree->getKey());

            if ((int) $lockedTree->batch_id !== (int) $lockedBatch->getKey()) {
                throw ValidationException::withMessages([
                    'tree' => 'Pohon tidak terdaftar pada kelompok penanaman ini.',
                ]);
            }

            $lockedTree->batch_id = null;
            $lockedTree->save();

            $metadata = [
                'removed_tree_id' => $lockedTree->getKey(),
                'code' => $lockedTree->code,
            ];

            if ($reason !== null) {
                $metadata['reason'] = $reason;
            }

            $lockedBatch->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'planting_batch.tree_removed',
                'from_status' => null,
                'to_status' => null,
                'metadata' => $metadata,
            ]);
        });
    }
}

