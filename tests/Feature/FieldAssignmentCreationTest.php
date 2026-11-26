<?php

namespace Tests\Feature;

use App\Actions\FieldAssignments\CreateFieldAssignment;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\FieldAssignments\Pages\CreateFieldAssignment as CreateFieldAssignmentPage;
use App\Filament\Manager\Resources\FieldAssignments\Pages\ListFieldAssignments;
use App\Models\Activity;
use App\Models\FieldAssignment;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class FieldAssignmentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_assign_field_staff_to_tree_in_activity_location_and_records_audit_log(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $activity = $this->activity($site);
        $tree = $this->tree($site);
        $staff = $this->fieldStaff();

        $this->openManagerPanelAs($manager);

        $dueAt = now()->addDays(3)->format('Y-m-d H:i:s');

        Livewire::test(CreateFieldAssignmentPage::class)
            ->fillForm([
                'activity_id' => $activity->getKey(),
                'tree_id' => $tree->getKey(),
                'assignee_id' => $staff->getKey(),
                'due_at' => $dueAt,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $assignment = FieldAssignment::query()
            ->where('activity_id', $activity->getKey())
            ->where('tree_id', $tree->getKey())
            ->where('assignee_id', $staff->getKey())
            ->sole();

        $this->assertSame('assigned', $assignment->status);
        $this->assertSame($dueAt, $assignment->due_at->format('Y-m-d H:i:s'));

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'field_assignment.created',
            'subject_type' => FieldAssignment::class,
            'subject_id' => $assignment->getKey(),
            'to_status' => 'assigned',
        ]);
    }

    public function test_cannot_assign_tree_outside_activity_location(): void
    {
        $manager = $this->manager();
        $siteA = $this->site($manager, 'Lokasi A');
        $siteB = $this->site($manager, 'Lokasi B');

        $activity = $this->activity($siteA);
        $treeInSiteB = $this->tree($siteB);
        $staff = $this->fieldStaff();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Pohon yang dipilih harus berada di lokasi kegiatan yang sama.');

        app(CreateFieldAssignment::class)->handle($manager, [
            'activity_id' => $activity->getKey(),
            'tree_id' => $treeInSiteB->getKey(),
            'assignee_id' => $staff->getKey(),
            'due_at' => now()->addDays(2)->toDateTimeString(),
        ]);
    }

    public function test_cannot_assign_with_past_due_date(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $activity = $this->activity($site);
        $tree = $this->tree($site);
        $staff = $this->fieldStaff();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tenggat penugasan tidak boleh berada di masa lalu.');

        app(CreateFieldAssignment::class)->handle($manager, [
            'activity_id' => $activity->getKey(),
            'tree_id' => $tree->getKey(),
            'assignee_id' => $staff->getKey(),
            'due_at' => now()->subDay()->toDateTimeString(),
        ]);
    }

    public function test_cannot_assign_user_who_is_not_field_staff(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $site = $this->site($manager);
        $activity = $this->activity($site);
        $tree = $this->tree($site);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Petugas yang dipilih harus memiliki peran petugas lapangan.');

        app(CreateFieldAssignment::class)->handle($manager, [
            'activity_id' => $activity->getKey(),
            'tree_id' => $tree->getKey(),
            'assignee_id' => $citizen->getKey(),
            'due_at' => now()->addDays(2)->toDateTimeString(),
        ]);
    }

    public function test_non_manager_cannot_create_field_assignment(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();
        $site = $this->site($manager);
        $activity = $this->activity($site);
        $tree = $this->tree($site);

        $this->expectException(AuthorizationException::class);

        app(CreateFieldAssignment::class)->handle($staff, [
            'activity_id' => $activity->getKey(),
            'tree_id' => $tree->getKey(),
            'assignee_id' => $staff->getKey(),
            'due_at' => now()->addDays(2)->toDateTimeString(),
        ]);
    }

    public function test_manager_can_view_field_assignment_table(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $activity = $this->activity($site);
        $tree = $this->tree($site);
        $staff = $this->fieldStaff();

        $assignment = FieldAssignment::query()->create([
            'activity_id' => $activity->getKey(),
            'tree_id' => $tree->getKey(),
            'assignee_id' => $staff->getKey(),
            'due_at' => now()->addDays(2),
            'status' => 'assigned',
        ]);

        $this->openManagerPanelAs($manager);

        Livewire::test(ListFieldAssignments::class)
            ->assertCanSeeTableRecords([$assignment])
            ->assertSee($activity->title)
            ->assertSee($tree->code)
            ->assertSee($staff->name);
    }

    public function test_field_assignment_policy_rules(): void
    {
        $manager = $this->manager();
        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $citizen = $this->citizen();

        $site = $this->site($manager);
        $activity = $this->activity($site);
        $tree = $this->tree($site);

        $assignment = new FieldAssignment([
            'activity_id' => $activity->getKey(),
            'tree_id' => $tree->getKey(),
            'assignee_id' => $staffA->getKey(),
            'due_at' => now()->addDays(2),
            'status' => 'assigned',
        ]);
        $assignment->forceFill(['id' => 1]);

        // Manager checks
        $this->assertTrue($manager->can('viewAny', FieldAssignment::class));
        $this->assertTrue($manager->can('create', FieldAssignment::class));
        $this->assertTrue($manager->can('view', $assignment));
        $this->assertTrue($manager->can('update', $assignment));

        // Field Staff checks
        $this->assertTrue($staffA->can('viewAny', FieldAssignment::class));
        $this->assertTrue($staffA->can('view', $assignment));
        $this->assertFalse($staffB->can('view', $assignment));
        $this->assertFalse($staffA->can('create', FieldAssignment::class));

        // Citizen checks
        $this->assertFalse($citizen->can('viewAny', FieldAssignment::class));
        $this->assertFalse($citizen->can('create', FieldAssignment::class));
        $this->assertFalse($citizen->can('view', $assignment));
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

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function site(User $manager, string $name = 'Taman Suropati'): Site
    {
        $program = Program::query()->create([
            'name' => 'Program Hijau ' . uniqid(),
            'objective' => 'Konservasi lingkungan.',
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        return Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => $name,
            'address' => 'Jl. Taman Hijau No. 1',
        ]);
    }

    private function activity(Site $site): Activity
    {
        return Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Penugasan ' . uniqid(),
            'kind' => 'care',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(3),
            'quota' => 20,
            'status' => 'open',
        ]);
    }

    private function tree(Site $site): Tree
    {
        $plot = Plot::query()->create([
            'site_id' => $site->getKey(),
            'code' => 'PLOT-' . uniqid(),
            'capacity' => 10,
            'status' => 'open',
        ]);

        $species = TreeSpecies::query()->create([
            'name' => 'Pohon Trembesi ' . uniqid(),
            'care_notes' => 'Perawatan rutin',
            'is_active' => true,
        ]);

        return Tree::query()->create([
            'plot_id' => $plot->getKey(),
            'species_id' => $species->getKey(),
            'code' => 'TREE-' . uniqid(),
            'planted_on' => now()->subMonth()->toDateString(),
            'initial_condition' => 'healthy',
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }
}
