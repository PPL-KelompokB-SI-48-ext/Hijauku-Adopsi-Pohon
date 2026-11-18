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
    public function handle(User $actor, PlantingBatch $batch, Tree $tree): void
    {
        Gate::forUser($actor)->authorize('update', $batch);

        DB::transaction(function () use ($actor, $batch, $tree): void {
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

            $lockedBatch->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'planting_batch.tree_removed',
                'from_status' => null,
                'to_status' => null,
                'metadata' => [
                    'removed_tree_id' => $lockedTree->getKey(),
                    'code' => $lockedTree->code,
                ],
            ]);
        });
    }
}

