<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Manager\Resources\PlantingBatches\Pages\EditPlantingBatch;
use App\Filament\Manager\Resources\PlantingBatches\Pages\ListPlantingBatches;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlantingBatchManagerInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private function createPlot(string $code = 'PLT-01'): Plot
    {
        $program = Program::factory()->create();
        $site = Site::factory()->create(['program_id' => $program->id]);

        return Plot::factory()->create([
            'site_id' => $site->id,
            'code' => $code,
        ]);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('app'));
    }

    public function test_manager_can_view_planting_batches_list_in_app_panel(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot('PLT-MGR-01');

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-MGR-001',
            'planted_on' => now()->subDays(3)->toDateString(),
        ]);

        $this->actingAs($manager);

        Livewire::test(ListPlantingBatches::class)
            ->assertSuccessful()
            ->assertSee('BATCH-MGR-001')
            ->assertSee('PLT-MGR-01');
    }

    public function test_manager_can_update_planting_batch_with_reason_from_edit_page(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-BEFORE',
            'planted_on' => now()->subDays(5)->toDateString(),
            'notes' => 'Catatan lama',
        ]);

        $this->actingAs($manager);

        Livewire::test(EditPlantingBatch::class, ['record' => $batch->getRouteKey()])
            ->assertSuccessful()
            ->fillForm([
                'code' => 'BATCH-AFTER',
                'notes' => 'Catatan setelah koreksi',
                'reason' => 'Koreksi administratif oleh pengelola.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('planting_batches', [
            'id' => $batch->id,
            'code' => 'BATCH-AFTER',
            'notes' => 'Catatan setelah koreksi',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->id,
            'actor_id' => $manager->id,
            'action' => 'planting_batch.updated',
        ]);
    }

    public function test_delete_action_is_hidden_when_batch_has_trees(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();
        $species = TreeSpecies::factory()->create(['is_active' => true]);

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-WITH-TREES',
            'planted_on' => now()->subDays(3)->toDateString(),
        ]);

        Tree::factory()->create([
            'plot_id' => $plot->id,
            'species_id' => $species->id,
            'batch_id' => $batch->id,
            'code' => 'TREE-DEL-01',
        ]);

        $this->actingAs($manager);

        Livewire::test(ListPlantingBatches::class)
            ->assertTableActionHidden('delete', $batch);
    }

    public function test_delete_action_is_visible_and_deletes_when_batch_is_empty(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-EMPTY-DEL',
            'planted_on' => now()->subDays(2)->toDateString(),
        ]);

        $this->actingAs($manager);

        Livewire::test(ListPlantingBatches::class)
            ->assertTableActionVisible('delete', $batch)
            ->callTableAction('delete', $batch);

        $this->assertDatabaseMissing('planting_batches', [
            'id' => $batch->id,
        ]);
    }

    public function test_non_manager_cannot_access_manager_planting_batch_resource(): void
    {
        $fieldStaff = $this->fieldStaff();
        $this->actingAs($fieldStaff);

        $response = $this->get('/app/planting-batches');
        $response->assertForbidden();
    }
}
