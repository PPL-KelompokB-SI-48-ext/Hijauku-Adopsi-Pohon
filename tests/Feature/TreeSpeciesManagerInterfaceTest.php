<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Manager\Resources\TreeSpecies\Pages\EditTreeSpecies;
use App\Filament\Manager\Resources\TreeSpecies\Pages\ListTreeSpecies;
use App\Models\Plot;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TreeSpeciesManagerInterfaceTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ setup

    private function manager(): User
    {
        return User::factory()->create([
            'role'      => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function species(array $overrides = []): TreeSpecies
    {
        return TreeSpecies::factory()->create(array_merge([
            'name'       => 'Trembesi',
            'care_notes' => 'Siram seminggu sekali.',
            'is_active'  => true,
        ], $overrides));
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }

    // ------------------------------------------------------------------ list page renders

    public function test_manager_can_view_tree_species_list_page(): void
    {
        $manager = $this->manager();
        $species = $this->species(['name' => 'Mahoni Uji']);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListTreeSpecies::class)
            ->assertSuccessful()
            ->assertSee($species->name);
    }

    // ------------------------------------------------------------------ edit page

    public function test_edit_page_updates_name_and_records_audit_log(): void
    {
        $manager = $this->manager();
        $species = $this->species(['name' => 'Mahoni Lama']);

        $this->openManagerPanelAs($manager);

        Livewire::test(EditTreeSpecies::class, ['record' => $species->getKey()])
            ->fillForm([
                'name'       => 'Mahoni Baru',
                'care_notes' => 'Catatan baru.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tree_species', ['name' => 'Mahoni Baru']);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action'   => 'tree_species.updated',
        ]);
    }

    // ------------------------------------------------------------------ deactivate table action

    public function test_deactivate_table_action_sets_species_inactive_and_records_audit(): void
    {
        $manager = $this->manager();
        $species = $this->species(['name' => 'Jati Aktif', 'is_active' => true]);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListTreeSpecies::class)
            ->callTableAction('deactivate', $species)
            ->assertHasNoTableActionErrors();

        $this->assertFalse((bool) $species->fresh()->is_active);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id'    => $manager->getKey(),
            'action'      => 'tree_species.deactivated',
            'from_status' => 'active',
            'to_status'   => 'inactive',
        ]);
    }

    // ------------------------------------------------------------------ delete table action (unused species)

    public function test_delete_table_action_removes_unused_species_and_records_audit(): void
    {
        $manager = $this->manager();
        $species = $this->species(['name' => 'Sengon Tanpa Pohon']);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListTreeSpecies::class)
            ->callTableAction('delete', $species)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('tree_species', ['id' => $species->getKey()]);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action'   => 'tree_species.deleted',
        ]);
    }

    // ------------------------------------------------------------------ delete table action hidden when in use

    public function test_delete_table_action_is_hidden_when_species_has_trees(): void
    {
        $manager = $this->manager();
        $species = $this->species(['name' => 'Beringin Terpakai']);

        // Pastikan ada pohon terdaftar dengan species ini
        Tree::factory()->create(['species_id' => $species->getKey()]);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListTreeSpecies::class)
            ->assertTableActionHidden('delete', $species);
    }

    // ------------------------------------------------------------------ deactivate action visible only for active species

    public function test_deactivate_table_action_is_hidden_when_species_already_inactive(): void
    {
        $manager = $this->manager();
        $species = $this->species(['name' => 'Trembesi Nonaktif', 'is_active' => false]);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListTreeSpecies::class)
            ->assertTableActionHidden('deactivate', $species);
    }
}
