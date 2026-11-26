<?php

namespace Tests\Feature;

use App\Actions\Trees\CreateTree;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Plots\Pages\ListPlots;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PlotCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_sees_a_warning_when_a_plot_reaches_90_percent_capacity(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $nearCapacity = Plot::factory()->create(['code' => 'WARN-90', 'capacity' => 10]);

        Tree::factory()->count(9)->create([
            'species_id' => $species->getKey(),
            'plot_id' => $nearCapacity->getKey(),
        ]);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListPlots::class)
            ->assertSee('9 Pohon')
            ->assertSee('90%')
            ->assertSee('Hampir penuh');
    }

    public function test_manager_does_not_see_a_warning_before_exactly_90_percent(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $plot = Plot::factory()->create(['code' => 'WARN-895', 'capacity' => 200]);

        Tree::factory()->count(179)->create([
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
        ]);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListPlots::class)
            ->assertSee('179 Pohon')
            ->assertSee('90%')
            ->assertDontSee('Hampir penuh');
    }

    public function test_field_staff_can_fill_the_last_slot_but_cannot_exceed_plot_capacity(): void
    {
        $staff = $this->fieldStaff();
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $plot = Plot::factory()->create(['capacity' => 2]);

        Tree::factory()->create([
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
            'code' => 'CAPACITY-EXISTING',
        ]);

        $this->assertSame(2, $plot->capacity);

        $created = app(CreateTree::class)->handle($staff, $this->treeAttributes($species, $plot, 'CAPACITY-LAST'));

        $this->assertSame($plot->getKey(), $created->plot_id);
        $this->assertSame(2, $plot->trees()->count());

        try {
            app(CreateTree::class)->handle($staff, $this->treeAttributes($species, $plot, 'CAPACITY-OVERFLOW'));

            $this->fail('Pohon yang melampaui kapasitas seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('plot_id', $exception->errors());
        }

        $this->assertSame(2, $plot->trees()->count());
        $this->assertDatabaseMissing('trees', ['code' => 'CAPACITY-OVERFLOW']);
    }

    public function test_tree_creation_is_limited_to_active_field_staff(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $plot = Plot::factory()->create(['capacity' => 10]);

        $this->expectException(AuthorizationException::class);

        app(CreateTree::class)->handle($manager, $this->treeAttributes($species, $plot, 'CAPACITY-WRONG-ROLE'));
    }

    public function test_tree_cannot_be_added_to_a_closed_plot(): void
    {
        $staff = $this->fieldStaff();
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $plot = Plot::factory()->create(['capacity' => 10, 'status' => 'closed']);

        try {
            app(CreateTree::class)->handle($staff, $this->treeAttributes($species, $plot, 'CLOSED-PLOT-TREE'));

            $this->fail('Petak tertutup seharusnya menolak pencatatan pohon baru.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('plot_id', $exception->errors());
        }

        $this->assertDatabaseMissing('trees', ['code' => 'CLOSED-PLOT-TREE']);
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

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }

    /**
     * @return array<string, mixed>
     */
    private function treeAttributes(TreeSpecies $species, Plot $plot, string $code): array
    {
        return [
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
            'batch_id' => null,
            'code' => $code,
            'planted_on' => now()->toDateString(),
            'initial_condition' => 'baik',
        ];
    }
}
