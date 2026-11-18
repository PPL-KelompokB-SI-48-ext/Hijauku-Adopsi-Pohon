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
     * Jika terdapat pohon yang dipindahkan dari kelompok lain, alasan koreksi wajib disertakan.
     *
     * @param  array<int, int>  $treeIds
     */
    public function handle(User $actor, PlantingBatch $batch, array $treeIds, ?string $reason = null): void
    {
        Gate::forUser($actor)->authorize('update', $batch);

        if (empty($treeIds)) {
            return;
        }

        DB::transaction(function () use ($actor, $batch, $treeIds, $reason): void {
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

            // Deteksi pohon yang sebelumnya sudah terdaftar pada kelompok lain
            $reassignedTrees = $trees->filter(function (Tree $tree) use ($lockedBatch): bool {
                return $tree->batch_id !== null && (int) $tree->batch_id !== (int) $lockedBatch->getKey();
            });

            $cleanReason = trim((string) $reason);
            if ($reassignedTrees->isNotEmpty() && mb_strlen($cleanReason) < 3) {
                throw ValidationException::withMessages([
                    'reason' => 'Alasan pemindahan pohon dari kelompok sebelumnya wajib diisi minimal 3 karakter.',
                ]);
            }

            // Catat jejak audit pada kelompok asal jika ada pohon yang dipindahkan
            $treesByOldBatch = $reassignedTrees->groupBy('batch_id');
            foreach ($treesByOldBatch as $oldBatchId => $oldTrees) {
                $oldBatch = PlantingBatch::query()->find($oldBatchId);
                if ($oldBatch) {
                    $oldBatch->activityLogs()->create([
                        'actor_id' => $actor->getKey(),
                        'action' => 'planting_batch.tree_reassigned',
                        'from_status' => null,
                        'to_status' => null,
                        'metadata' => [
                            'target_batch_id' => $lockedBatch->getKey(),
                            'target_batch_code' => $lockedBatch->code,
                            'tree_ids' => $oldTrees->pluck('id')->all(),
                            'tree_codes' => $oldTrees->pluck('code')->all(),
                            'reason' => $cleanReason,
                        ],
                    ]);
                }
            }

            foreach ($trees as $tree) {
                $tree->batch_id = $lockedBatch->getKey();
                $tree->save();
            }

            $metadata = [
                'assigned_tree_ids' => $trees->pluck('id')->all(),
                'count' => $trees->count(),
            ];

            if ($reassignedTrees->isNotEmpty()) {
                $metadata['reassigned_tree_ids'] = $reassignedTrees->pluck('id')->all();
                $metadata['reason'] = $cleanReason;
            }

            $lockedBatch->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'planting_batch.trees_assigned',
                'from_status' => null,
                'to_status' => null,
                'metadata' => $metadata,
            ]);
        });
    }
}
