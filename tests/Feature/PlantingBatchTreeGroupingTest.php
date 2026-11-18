<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\AssignTreesToPlantingBatch;
use App\Actions\PlantingBatches\RemoveTreeFromPlantingBatch;
use App\Enums\UserRole;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchTreeGroupingTest extends TestCase
{
    use RefreshDatabase;

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    public function test_field_staff_can_assign_trees_in_same_plot_to_batch(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);

        $tree1 = Tree::factory()->create(['plot_id' => $plot->getKey(), 'batch_id' => null]);
        $tree2 = Tree::factory()->create(['plot_id' => $plot->getKey(), 'batch_id' => null]);

        $action = app(AssignTreesToPlantingBatch::class);
        $action->handle($staff, $batch, [$tree1->getKey(), $tree2->getKey()]);

        $this->assertSame($batch->getKey(), $tree1->fresh()->batch_id);
        $this->assertSame($batch->getKey(), $tree2->fresh()->batch_id);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'planting_batch.trees_assigned',
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->getKey(),
        ]);
    }

    public function test_cannot_assign_tree_from_different_plot_to_batch(): void
    {
        $staff = $this->fieldStaff();
        $plotA = Plot::factory()->create();
        $plotB = Plot::factory()->create();

        $batch = PlantingBatch::factory()->create(['plot_id' => $plotA->getKey()]);
        $treeInPlotB = Tree::factory()->create(['plot_id' => $plotB->getKey(), 'batch_id' => null]);

        $action = app(AssignTreesToPlantingBatch::class);

        try {
            $action->handle($staff, $batch, [$treeInPlotB->getKey()]);
            $this->fail('Seharusnya gagal jika pohon berada di petak berbeda.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tree_ids', $exception->errors());
        }

        $this->assertNull($treeInPlotB->fresh()->batch_id);
    }

    public function test_tree_cannot_belong_to_multiple_batches_and_reassignment_updates_batch(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();

        $batch1 = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);
        $batch2 = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);

        $tree = Tree::factory()->create(['plot_id' => $plot->getKey(), 'batch_id' => $batch1->getKey()]);

        // Satu pohon hanya punya satu batch_id di DB
        $this->assertSame($batch1->getKey(), $tree->batch_id);

        $action = app(AssignTreesToPlantingBatch::class);
        $action->handle($staff, $batch2, [$tree->getKey()]);

        // Berpindah ke batch2, tidak mungkin di batch1 dan batch2 bersamaan
        $this->assertSame($batch2->getKey(), $tree->fresh()->batch_id);
    }

    public function test_field_staff_can_remove_tree_from_batch(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);

        $tree = Tree::factory()->create(['plot_id' => $plot->getKey(), 'batch_id' => $batch->getKey()]);

        $action = app(RemoveTreeFromPlantingBatch::class);
        $action->handle($staff, $batch, $tree);

        $this->assertNull($tree->fresh()->batch_id);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'planting_batch.tree_removed',
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->getKey(),
        ]);
    }

    public function test_non_field_staff_cannot_group_trees(): void
    {
        $citizen = $this->citizen();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);
        $tree = Tree::factory()->create(['plot_id' => $plot->getKey(), 'batch_id' => null]);

        $action = app(AssignTreesToPlantingBatch::class);

        $this->expectException(AuthorizationException::class);
        $action->handle($citizen, $batch, [$tree->getKey()]);
    }
}
