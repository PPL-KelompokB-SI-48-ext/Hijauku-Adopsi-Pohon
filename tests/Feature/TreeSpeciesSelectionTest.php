<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreeSpeciesSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_only_active_species_ordered_by_name(): void
    {
        TreeSpecies::factory()->create(['name' => 'Trembesi', 'is_active' => true]);
        TreeSpecies::factory()->create(['name' => 'Akasia', 'is_active' => true]);
        TreeSpecies::factory()->create(['name' => 'Mahoni', 'is_active' => false]);

        $results = TreeSpecies::active()->pluck('name');

        $this->assertCount(2, $results);
        $this->assertSame('Akasia', $results->first());
        $this->assertSame('Trembesi', $results->last());
        $this->assertNotContains('Mahoni', $results->toArray());
    }

    public function test_citizen_cannot_view_any_tree_species(): void
    {
        $citizen = User::factory()->create(['role' => UserRole::Citizen, 'is_active' => true]);
        $this->assertFalse($citizen->can('viewAny', TreeSpecies::class));
    }

    public function test_field_staff_cannot_create_update_or_delete_tree_species(): void
    {
        $petugas = User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);
        $species = TreeSpecies::factory()->create(['is_active' => true]);

        $this->assertFalse($petugas->can('create', TreeSpecies::class));
        $this->assertFalse($petugas->can('update', $species));
        $this->assertFalse($petugas->can('delete', $species));
    }

    public function test_field_staff_can_view_any_and_create_trees(): void
    {
        $petugas = User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);

        $this->assertTrue($petugas->can('viewAny', \App\Models\Tree::class));
        $this->assertTrue($petugas->can('create', \App\Models\Tree::class));
    }

    public function test_citizen_cannot_view_any_or_create_trees(): void
    {
        $citizen = User::factory()->create(['role' => UserRole::Citizen, 'is_active' => true]);

        $this->assertFalse($citizen->can('viewAny', \App\Models\Tree::class));
        $this->assertFalse($citizen->can('create', \App\Models\Tree::class));
    }

    public function test_field_staff_cannot_update_or_delete_trees_in_this_story(): void
    {
        $petugas = User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);
        $tree = new \App\Models\Tree;
        $tree->forceFill(['id' => 1]);

        $this->assertFalse($petugas->can('update', $tree));
        $this->assertFalse($petugas->can('delete', $tree));
    }

    public function test_species_dropdown_only_offers_active_species(): void
    {
        $aktif = TreeSpecies::factory()->create(['name' => 'Trembesi', 'is_active' => true]);
        $nonaktif = TreeSpecies::factory()->create(['name' => 'Mahoni', 'is_active' => false]);

        $this->openFieldStaffPanelAs($this->fieldStaff());

        $component = \Livewire\Livewire::test(\App\Filament\FieldStaff\Resources\Trees\Pages\CreateTree::class)
            ->instance()
            ->form
            ->getComponent('species_id');

        $options = $component instanceof \Filament\Forms\Components\Select
            ? $component->getOptions()
            : [];

        $this->assertArrayHasKey($aktif->getKey(), $options);
        $this->assertArrayNotHasKey($nonaktif->getKey(), $options);
    }

    public function test_creating_tree_with_inactive_species_is_rejected(): void
    {
        $nonaktif = TreeSpecies::factory()->create(['name' => 'Mahoni', 'is_active' => false]);
        $plot = \App\Models\Plot::factory()->create();

        $this->openFieldStaffPanelAs($this->fieldStaff());

        \Livewire\Livewire::test(\App\Filament\FieldStaff\Resources\Trees\Pages\CreateTree::class)
            ->fillForm([
                'species_id' => $nonaktif->getKey(),
                'plot_id' => $plot->getKey(),
                'code' => 'PHN-001',
                'initial_condition' => 'baik',
            ])
            ->call('create')
            ->assertHasFormErrors(['species_id']);

        $this->assertDatabaseCount('trees', 0);
    }

    public function test_field_staff_can_create_tree_with_active_species(): void
    {
        $aktif = TreeSpecies::factory()->create(['name' => 'Trembesi', 'is_active' => true]);
        $plot = \App\Models\Plot::factory()->create();

        $this->openFieldStaffPanelAs($this->fieldStaff());

        \Livewire\Livewire::test(\App\Filament\FieldStaff\Resources\Trees\Pages\CreateTree::class)
            ->fillForm([
                'species_id' => $aktif->getKey(),
                'plot_id' => $plot->getKey(),
                'code' => 'PHN-VALID-01',
                'initial_condition' => 'sehat',
                'planted_on' => '2026-10-30',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('trees', [
            'species_id' => $aktif->getKey(),
            'plot_id' => $plot->getKey(),
            'code' => 'PHN-VALID-01',
            'initial_condition' => 'sehat',
        ]);
    }

    public function test_tree_list_shows_trees_whose_species_is_now_inactive(): void
    {
        $nonaktif = TreeSpecies::factory()->create(['name' => 'Mahoni', 'is_active' => false]);
        $plot = \App\Models\Plot::factory()->create();
        \App\Models\Tree::factory()->create([
            'species_id' => $nonaktif->getKey(),
            'plot_id' => $plot->getKey(),
            'code' => 'PHN-LAMA',
            'initial_condition' => 'baik',
        ]);

        $this->openFieldStaffPanelAs($this->fieldStaff());

        \Livewire\Livewire::test(\App\Filament\FieldStaff\Resources\Trees\Pages\ListTrees::class)
            ->assertSee('PHN-LAMA')
            ->assertSee('Mahoni');
    }

    public function test_citizen_is_forbidden_from_tree_panel_routes(): void
    {
        $citizen = User::factory()->create(['role' => UserRole::Citizen, 'is_active' => true]);

        $this->actingAs($citizen)
            ->get('/petugas/trees')
            ->assertForbidden();
    }

    private function fieldStaff(): User
    {
        return User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);
    }

    private function openFieldStaffPanelAs(User $user): void
    {
        $this->actingAs($user);
        \Filament\Facades\Filament::setCurrentPanel('petugas');
    }
}
