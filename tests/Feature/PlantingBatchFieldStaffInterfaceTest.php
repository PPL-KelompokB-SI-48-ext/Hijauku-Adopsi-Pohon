<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\PlantingBatches\Pages\CreatePlantingBatch;
use App\Filament\FieldStaff\Resources\PlantingBatches\Pages\ListPlantingBatches;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlantingBatchFieldStaffInterfaceTest extends TestCase
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

    public function test_field_staff_can_view_planting_batch_list_page(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create(['code' => 'PLT-TEST-01']);
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-TEST-LIST',
        ]);

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(ListPlantingBatches::class)
            ->assertSuccessful()
            ->assertSee($batch->code)
            ->assertSee($plot->code);
    }

    public function test_field_staff_can_create_planting_batch_via_form(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create(['code' => 'PLT-A01']);

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(CreatePlantingBatch::class)
            ->fillForm([
                'plot_id' => $plot->getKey(),
                'code' => 'BATCH-NEW-01',
                'planted_on' => now()->toDateString(),
                'notes' => 'Catatan penanaman via form.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('planting_batches', [
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-NEW-01',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'planting_batch.created',
        ]);
    }

    public function test_form_validates_future_planting_date(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(CreatePlantingBatch::class)
            ->fillForm([
                'plot_id' => $plot->getKey(),
                'code' => 'BATCH-FUTURE-UI',
                'planted_on' => now()->addDay()->toDateString(),
            ])
            ->call('create')
            ->assertHasFormErrors(['planted_on' => 'before_or_equal']);
    }

    public function test_form_validates_duplicate_code_for_same_plot(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-EXISTING',
        ]);

        $this->openFieldStaffPanelAs($staff);

        Livewire::test(CreatePlantingBatch::class)
            ->fillForm([
                'plot_id' => $plot->getKey(),
                'code' => 'BATCH-EXISTING',
                'planted_on' => now()->toDateString(),
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }
}
