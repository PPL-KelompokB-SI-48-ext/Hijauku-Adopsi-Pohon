<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Manager\Resources\PlantingBatches\Pages\EditPlantingBatch;
use App\Filament\Manager\Resources\PlantingBatches\Pages\ListPlantingBatches;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlantingBatchManagerInterfaceTest extends TestCase
{
    use RefreshDatabase;

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

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    public function test_manager_can_view_planting_batches_list_in_app_panel(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create(['code' => 'PLOT-Z99']);
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-VIEW-01',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ListPlantingBatches::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$batch])
            ->assertSee('BATCH-VIEW-01')
            ->assertSee('PLOT-Z99');
    }

    public function test_manager_can_update_planting_batch_with_reason_from_edit_page(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-BEFORE',
            'notes' => 'Catatan awal',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(EditPlantingBatch::class, ['record' => $batch->getKey()])
            ->assertSuccessful()
            ->fillForm([
                'code' => 'BATCH-AFTER',
                'notes' => 'Catatan diperbarui manajer.',
                'reason' => 'Koreksi penulisan dan penambahan catatan.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('planting_batches', [
            'id' => $batch->getKey(),
            'code' => 'BATCH-AFTER',
            'notes' => 'Catatan diperbarui manajer.',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'planting_batch.updated',
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->getKey(),
        ]);
    }

    public function test_update_fails_without_reason_on_edit_page(): void
    {
        $manager = $this->manager();
        $batch = PlantingBatch::factory()->create();

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(EditPlantingBatch::class, ['record' => $batch->getKey()])
            ->assertSuccessful()
            ->fillForm([
                'code' => 'BATCH-NO-REASON',
                'reason' => '',
            ])
            ->call('save')
            ->assertHasFormErrors(['reason' => 'required']);
    }

    public function test_delete_action_is_hidden_when_batch_has_trees(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batchWithTrees = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-OCCUPIED',
        ]);
        Tree::factory()->create([
            'plot_id' => $plot->getKey(),
            'batch_id' => $batchWithTrees->getKey(),
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ListPlantingBatches::class)
            ->assertSuccessful()
            ->assertTableActionHidden('delete', $batchWithTrees);
    }

    public function test_delete_action_is_visible_and_executable_when_batch_is_empty(): void
    {
        $manager = $this->manager();
        $emptyBatch = PlantingBatch::factory()->create([
            'code' => 'BATCH-EMPTY-DEL',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ListPlantingBatches::class)
            ->assertSuccessful()
            ->assertTableActionVisible('delete', $emptyBatch)
            ->callTableAction('delete', $emptyBatch);

        $this->assertDatabaseMissing('planting_batches', [
            'id' => $emptyBatch->getKey(),
        ]);
    }

    public function test_field_staff_and_citizen_cannot_access_manager_planting_batch_resource(): void
    {
        $fieldStaff = $this->fieldStaff();
        $citizen = $this->citizen();

        // Field staff di panel /app
        $this->actingAs($fieldStaff);
        $responseStaff = $this->get('/app/planting-batches');
        $this->assertTrue(in_array($responseStaff->status(), [403, 302]));

        // Warga di panel /app
        $this->actingAs($citizen);
        $responseCitizen = $this->get('/app/planting-batches');
        $this->assertTrue(in_array($responseCitizen->status(), [403, 302]));
    }
}
