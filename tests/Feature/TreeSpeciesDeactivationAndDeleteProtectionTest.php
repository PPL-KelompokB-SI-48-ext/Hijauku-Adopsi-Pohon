<?php

namespace Tests\Feature;

use App\Actions\TreeSpecies\DeactivateTreeSpecies;
use App\Actions\TreeSpecies\DeleteTreeSpecies;
use App\Enums\UserRole;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TreeSpeciesDeactivationAndDeleteProtectionTest extends TestCase
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

    public function test_tree_species_used_by_trees_cannot_be_deleted(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::create([
            'name' => 'Beringin Rindang',
            'is_active' => true,
        ]);

        $plot = Plot::factory()->create();

        Tree::create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => 'BER-001',
            'planted_on' => now()->toDateString(),
            'initial_condition' => 'baik',
        ]);

        $this->assertSame(1, $species->trees()->count());

        $action = app(DeleteTreeSpecies::class);

        try {
            $action->handle($manager, $species);
            $this->fail('Seharusnya melempar ValidationException ketika menghapus jenis pohon yang sudah dipakai inventaris.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tree_species', $exception->errors());
        }

        $this->assertDatabaseHas('tree_species', [
            'id' => $species->id,
            'name' => 'Beringin Rindang',
        ]);
    }

    public function test_unused_tree_species_can_be_deleted_with_audit_log(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::create([
            'name' => 'Pohon Sementara',
            'is_active' => true,
        ]);

        $action = app(DeleteTreeSpecies::class);
        $action->handle($manager, $species, 'Salah input data');

        $this->assertDatabaseMissing('tree_species', [
            'id' => $species->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'tree_species.deleted',
            'subject_type' => TreeSpecies::class,
            'subject_id' => $species->id,
        ]);
    }

    public function test_used_tree_species_can_be_deactivated_instead_of_deleted(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::create([
            'name' => 'Pohon Akasia',
            'is_active' => true,
        ]);

        $plot = Plot::factory()->create();

        Tree::create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => 'AKA-001',
            'planted_on' => now()->toDateString(),
            'initial_condition' => 'baik',
        ]);

        $action = app(DeactivateTreeSpecies::class);
        $deactivated = $action->handle($manager, $species, 'Bibit sudah tidak tersedia lagi di pasaran');

        $this->assertFalse($deactivated->fresh()->is_active);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'tree_species.deactivated',
            'subject_type' => TreeSpecies::class,
            'subject_id' => $species->id,
            'from_status' => 'active',
            'to_status' => 'inactive',
        ]);

        $log = $species->activityLogs()->latest('id')->firstOrFail();
        $this->assertSame('Bibit sudah tidak tersedia lagi di pasaran', $log->metadata['reason'] ?? null);
    }

    public function test_non_manager_cannot_delete_or_deactivate_tree_species(): void
    {
        $species = TreeSpecies::create([
            'name' => 'Pohon Lindung',
            'is_active' => true,
        ]);

        $fieldStaff = $this->fieldStaff();

        $this->expectException(AuthorizationException::class);
        app(DeleteTreeSpecies::class)->handle($fieldStaff, $species);
    }
}
