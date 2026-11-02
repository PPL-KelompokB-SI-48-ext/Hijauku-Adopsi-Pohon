<?php

namespace Tests\Feature;

use App\Actions\TreeSpecies\CreateTreeSpecies;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\TreeSpecies\Pages\CreateTreeSpecies as CreateTreeSpeciesPage;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class TreeSpeciesCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_tree_species_and_audit_event(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateTreeSpeciesPage::class)
            ->fillForm([
                'name' => 'Trembesi (Samanea saman)',
                'care_notes' => 'Pohon peneduh besar, butuh ruang perakaran luas dan air sedang.',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $species = TreeSpecies::query()->where('name', 'Trembesi (Samanea saman)')->sole();

        $this->assertTrue($species->is_active);
        $this->assertSame('Pohon peneduh besar, butuh ruang perakaran luas dan air sedang.', $species->care_notes);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'tree_species.created',
            'subject_type' => TreeSpecies::class,
            'subject_id' => $species->getKey(),
            'to_status' => 'active',
        ]);
    }

    public function test_tree_species_form_requires_name(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateTreeSpeciesPage::class)
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);

        $this->assertDatabaseCount('tree_species', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_tree_species_name_must_be_unique(): void
    {
        TreeSpecies::query()->create([
            'name' => 'Mahoni',
            'care_notes' => 'Catatan awal mahoni.',
            'is_active' => true,
        ]);

        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateTreeSpeciesPage::class)
            ->fillForm([
                'name' => 'Mahoni',
                'care_notes' => 'Duplikasi nama jenis pohon.',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'unique',
            ]);

        $this->assertDatabaseCount('tree_species', 1);
    }

    public function test_whitespace_only_name_is_rejected(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateTreeSpeciesPage::class)
            ->fillForm([
                'name' => "   \t  ",
                'care_notes' => 'Catatan pohon.',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);

        $this->assertDatabaseCount('tree_species', 0);
    }

    public function test_creation_action_cleans_empty_care_notes_and_defaults_active(): void
    {
        $manager = $this->manager();

        $species = app(CreateTreeSpecies::class)->handle($manager, [
            'name' => 'Bungur',
            'care_notes' => '   ',
        ]);

        $this->assertSame('Bungur', $species->name);
        $this->assertNull($species->care_notes);
        $this->assertTrue($species->is_active);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'tree_species.created',
            'subject_type' => TreeSpecies::class,
            'subject_id' => $species->getKey(),
            'to_status' => 'active',
        ]);
    }

    public function test_creation_action_rejects_duplicate_name(): void
    {
        $manager = $this->manager();

        TreeSpecies::query()->create([
            'name' => 'Angsana',
            'is_active' => true,
        ]);

        try {
            app(CreateTreeSpecies::class)->handle($manager, [
                'name' => 'Angsana',
            ]);

            $this->fail('Action seharusnya menolak nama jenis pohon duplikat.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }

        $this->assertDatabaseCount('tree_species', 1);
    }

    public function test_only_managers_can_open_tree_species_pages_or_call_creation_action(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $this->actingAs($fieldStaff)
            ->get('/app/tree-species')
            ->assertForbidden();

        $this->assertFalse($fieldStaff->can('viewAny', TreeSpecies::class));
        $this->assertFalse($citizen->can('viewAny', TreeSpecies::class));

        $this->expectException(AuthorizationException::class);

        app(CreateTreeSpecies::class)->handle($fieldStaff, [
            'name' => 'Pohon Ilegal',
        ]);
    }

    public function test_manager_is_not_allowed_to_update_or_delete_tree_species_in_this_story(): void
    {
        $manager = $this->manager();
        $species = new TreeSpecies;
        $species->forceFill(['id' => 10, 'name' => 'Sengon', 'is_active' => true]);

        $this->assertTrue($manager->can('create', TreeSpecies::class));
        $this->assertTrue($manager->can('viewAny', TreeSpecies::class));
        $this->assertFalse($manager->can('update', $species));
        $this->assertFalse($manager->can('delete', $species));
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }
}
