<?php

namespace Tests\Feature;

use App\Actions\TreeSpecies\UpdateTreeSpecies;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TreeSpeciesAuditAndUpdateTest extends TestCase
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

    public function test_manager_can_update_tree_species_and_audit_log_records_name_and_care_notes_changes(): void
    {
        $manager = $this->manager();
        $species = TreeSpecies::create([
            'name' => 'Trembesi Tua',
            'care_notes' => 'Penyiraman 2 hari sekali',
            'is_active' => true,
        ]);

        $action = app(UpdateTreeSpecies::class);
        $updated = $action->handle($manager, $species, [
            'name' => 'Trembesi Super',
            'care_notes' => 'Penyiraman setiap pagi',
            'reason' => 'Pembaruan nama varietas dan protokol perawatan',
        ]);

        $this->assertSame('Trembesi Super', $updated->fresh()->name);
        $this->assertSame('Penyiraman setiap pagi', $updated->fresh()->care_notes);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'tree_species.updated',
            'subject_type' => TreeSpecies::class,
            'subject_id' => $species->getKey(),
        ]);

        $log = $species->activityLogs()->latest('id')->firstOrFail();
        $this->assertSame('Trembesi Tua', $log->metadata['old']['name'] ?? null);
        $this->assertSame('Trembesi Super', $log->metadata['new']['name'] ?? null);
        $this->assertSame('Penyiraman 2 hari sekali', $log->metadata['old']['care_notes'] ?? null);
        $this->assertSame('Penyiraman setiap pagi', $log->metadata['new']['care_notes'] ?? null);
        $this->assertSame('Pembaruan nama varietas dan protokol perawatan', $log->metadata['reason'] ?? null);
    }

    public function test_update_validates_duplicate_name_with_other_records_but_allows_same_record_name(): void
    {
        $manager = $this->manager();
        TreeSpecies::create([
            'name' => 'Mahoni',
            'is_active' => true,
        ]);
        $species = TreeSpecies::create([
            'name' => 'Sengon',
            'is_active' => true,
        ]);

        $action = app(UpdateTreeSpecies::class);

        // Name conflict with other record
        try {
            $action->handle($manager, $species, [
                'name' => 'Mahoni',
            ]);
            $this->fail('Harus gagal validasi jika nama sama dengan jenis pohon lain.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }

        // Updating with the same name on self should be allowed
        $updated = $action->handle($manager, $species, [
            'name' => 'Sengon',
            'care_notes' => 'Catatan baru saja',
        ]);
        $this->assertSame('Catatan baru saja', $updated->fresh()->care_notes);
    }

    public function test_non_manager_cannot_update_tree_species(): void
    {
        $species = TreeSpecies::create([
            'name' => 'Beringin',
            'is_active' => true,
        ]);

        $action = app(UpdateTreeSpecies::class);

        $this->expectException(AuthorizationException::class);
        $action->handle($this->fieldStaff(), $species, [
            'name' => 'Beringin Baru',
        ]);
    }
}

