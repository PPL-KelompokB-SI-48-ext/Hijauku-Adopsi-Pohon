<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\PlantingBatches\Pages\ListPlantingBatches;
use App\Filament\FieldStaff\Resources\Trees\Pages\CreateTree;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlantingBatchTreeInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    private function openFieldStaffPanelAs(User $staff): void
    {
        $this->actingAs($staff);
        Filament::setCurrentPanel('petugas');
    }

    public function test_field_staff_can_open_tree_list_modal_for_batch(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create(['plot_id' => $plot->getKey(), 'code' => 'BATCH-VIEW-01']);

        $tree = Tree::factory()->create([
            'plot_id' => $plot->getKey(),
            'batch_id' => $batch->getKey(),
            'code' => 'TREE-BATCHED-01',
        ]);

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(ListPlantingBatches::class)
            ->assertTableActionExists('viewTrees')
            ->assertTableActionVisible('viewTrees', $batch);

        // Verifikasi bahwa data pohon terikat dapat diakses melalui batch
        $this->assertTrue($batch->trees->contains($tree));
        $this->assertSame('TREE-BATCHED-01', $batch->trees()->first()->code);
    }

    public function test_field_staff_can_assign_tree_to_batch_via_table_action(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);

        $tree = Tree::factory()->create([
            'plot_id' => $plot->getKey(),
            'batch_id' => null,
            'code' => 'TREE-UNASSIGNED-01',
        ]);

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(ListPlantingBatches::class)
            ->assertTableActionExists('assignTrees')
            ->callTableAction('assignTrees', $batch, data: [
                'tree_ids' => [$tree->getKey()],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame($batch->getKey(), $tree->fresh()->batch_id);
    }

    public function test_field_staff_can_select_batch_when_creating_tree(): void
    {
        $staff = $this->fieldStaff();
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create(['plot_id' => $plot->getKey()]);

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(CreateTree::class)
            ->fillForm([
                'species_id' => $species->getKey(),
                'plot_id' => $plot->getKey(),
                'batch_id' => $batch->getKey(),
                'code' => 'PHN-BATCHED-99',
                'planted_on' => now()->toDateString(),
                'initial_condition' => 'baik',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('trees', [
            'code' => 'PHN-BATCHED-99',
            'plot_id' => $plot->getKey(),
            'batch_id' => $batch->getKey(),
        ]);
    }
}
