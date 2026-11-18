<?php

namespace App\Actions\PlantingBatches;

use App\Models\PlantingBatch;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AssignTreesToPlantingBatch
{
    /**
     * Masukkan daftar pohon ke dalam kelompok penanaman.
     *
     * @param  array<int, int>  $treeIds
     */
    public function handle(User $actor, PlantingBatch $batch, array $treeIds): void
    {
        Gate::forUser($actor)->authorize('update', $batch);

        if (empty($treeIds)) {
            return;
        }

        DB::transaction(function () use ($actor, $batch, $treeIds): void {
            $lockedBatch = PlantingBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->getKey());

            $trees = Tree::query()
                ->whereIn('id', $treeIds)
                ->lockForUpdate()
                ->get();

            $invalidPlotTreeIds = [];
            foreach ($trees as $tree) {
                if ((int) $tree->plot_id !== (int) $lockedBatch->plot_id) {
                    $invalidPlotTreeIds[] = $tree->getKey();
                }
            }

            if (! empty($invalidPlotTreeIds)) {
                throw ValidationException::withMessages([
                    'tree_ids' => 'Pohon yang dimasukkan harus berada pada petak yang sama dengan kelompok penanaman.',
                ]);
            }

            foreach ($trees as $tree) {
                $tree->batch_id = $lockedBatch->getKey();
                $tree->save();
            }

            $lockedBatch->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'planting_batch.trees_assigned',
                'from_status' => null,
                'to_status' => null,
                'metadata' => [
                    'assigned_tree_ids' => $trees->pluck('id')->all(),
                    'count' => $trees->count(),
                ],
            ]);
        });
    }
}
