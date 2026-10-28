<?php

namespace Tests\Feature;

use App\Actions\CarePlans\CreateCarePlan;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\CarePlans\Pages\CreateCarePlan as CreateCarePlanPage;
use App\Models\CarePlan;
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

class CarePlanCreationTest extends TestCase
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

    private function createTree(string $code = 'PH-001'): Tree
    {
        $program = Program::query()->firstOrCreate([
            'name' => 'Program Hijau',
        ], [
            'objective' => 'Konservasi lingkungan',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
        ]);

        $site = Site::query()->firstOrCreate([
            'program_id' => $program->id,
            'name' => 'Taman Kota',
        ], [
            'address' => 'Jl. Hijau No. 1',
        ]);

        $plot = Plot::query()->firstOrCreate([
            'site_id' => $site->id,
            'code' => 'A-01',
        ], [
            'capacity' => 50,
        ]);

        $species = TreeSpecies::query()->firstOrCreate([
            'name' => 'Trembesi',
        ], [
            'care_notes' => 'Penyiraman teratur',
        ]);

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-02-01',
            'initial_condition' => 'good',
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }

    public function test_manager_can_create_direct_care_plan_without_assignment(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-001');

        Livewire::test(CreateCarePlanPage::class)
            ->fillForm([
                'tree_id' => $tree->id,
                'assignment_id' => null,
                'planned_on' => '2026-11-05',
                'action' => 'Pemupukan organik berkala dan penggemburan tanah sekitar akar.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $plan = CarePlan::query()->where('tree_id', $tree->id)->sole();

        $this->assertNull($plan->assignment_id);
        $this->assertSame('2026-11-05', $plan->planned_on->format('Y-m-d'));
        $this->assertSame('Pemupukan organik berkala dan penggemburan tanah sekitar akar.', $plan->action);
        $this->assertSame('draft', $plan->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.created',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
            'to_status' => 'draft',
        ]);
    }

    public function test_manager_can_create_care_plan_linked_to_field_assignment(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-002');

        $activity = \App\Models\Activity::query()->create([
            'site_id' => $tree->plot->site_id,
            'title' => 'Perawatan Rutin',
            'kind' => 'care',
            'starts_at' => '2026-11-08 08:00:00',
            'ends_at' => '2026-11-08 12:00:00',
            'status' => 'scheduled',
            'quota' => 10,
        ]);

        $assignment = FieldAssignment::query()->create([
            'activity_id' => $activity->id,
            'tree_id' => $tree->id,
            'assignee_id' => $staff->id,
            'due_at' => '2026-11-10 17:00:00',
            'status' => 'assigned',
        ]);

        Livewire::test(CreateCarePlanPage::class)
            ->fillForm([
                'tree_id' => $tree->id,
                'assignment_id' => $assignment->id,
                'planned_on' => '2026-11-08',
                'action' => 'Pembersihan gulma dan pemasangan mulsa.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $plan = CarePlan::query()->where('tree_id', $tree->id)->sole();

        $this->assertSame($assignment->id, $plan->assignment_id);
        $this->assertSame('draft', $plan->status);
    }

    public function test_care_plan_form_requires_tree_target_date_and_action(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateCarePlanPage::class)
            ->call('create')
            ->assertHasFormErrors([
                'tree_id' => 'required',
                'planned_on' => 'required',
                'action' => 'required',
            ]);

        $this->assertDatabaseCount('care_plans', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_whitespace_only_action_is_rejected(): void
    {
        $this->openManagerPanelAs($this->manager());
        $tree = $this->createTree('PH-003');

        Livewire::test(CreateCarePlanPage::class)
            ->fillForm([
                'tree_id' => $tree->id,
                'planned_on' => '2026-11-05',
                'action' => '    ',
            ])
            ->call('create')
            ->assertHasFormErrors(['action' => 'required']);

        $this->assertDatabaseCount('care_plans', 0);
    }

    public function test_creation_action_rejects_mismatched_assignment_and_tree(): void
    {
        $treeA = $this->createTree('PH-101');
        $treeB = $this->createTree('PH-102');
        $staff = $this->fieldStaff();

        $activity = \App\Models\Activity::query()->create([
            'site_id' => $treeB->plot->site_id,
            'title' => 'Perawatan Rutin B',
            'kind' => 'care',
            'starts_at' => '2026-11-08 08:00:00',
            'ends_at' => '2026-11-08 12:00:00',
            'status' => 'scheduled',
            'quota' => 10,
        ]);

        $assignmentForB = FieldAssignment::query()->create([
            'activity_id' => $activity->id,
            'tree_id' => $treeB->id,
            'assignee_id' => $staff->id,
            'due_at' => '2026-11-10 17:00:00',
            'status' => 'assigned',
        ]);

        $this->expectException(ValidationException::class);

        app(CreateCarePlan::class)->handle($this->manager(), [
            'tree_id' => $treeA->id,
            'assignment_id' => $assignmentForB->id,
            'planned_on' => '2026-11-05',
            'action' => 'Pemeriksaan akar',
        ]);
    }

    public function test_non_manager_cannot_create_care_plan(): void
    {
        $tree = $this->createTree('PH-200');
        $fieldStaff = $this->fieldStaff();
        $citizen = $this->citizen();

        $this->actingAs($fieldStaff)
            ->get('/app/care-plans')
            ->assertForbidden();

        $this->assertFalse($fieldStaff->can('viewAny', CarePlan::class));
        $this->assertFalse($fieldStaff->can('create', CarePlan::class));
        $this->assertFalse($citizen->can('viewAny', CarePlan::class));
        $this->assertFalse($citizen->can('create', CarePlan::class));

        $this->expectException(AuthorizationException::class);

        app(CreateCarePlan::class)->handle($fieldStaff, [
            'tree_id' => $tree->id,
            'planned_on' => '2026-11-05',
            'action' => 'Aksi oleh staf tidak diizinkan',
        ]);
    }

    public function test_manager_can_publish_draft_care_plan(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-300');

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => '2026-11-12',
            'action' => 'Pembersihan sanitasi',
            'status' => 'draft',
        ]);

        Livewire::test(\App\Filament\Manager\Resources\CarePlans\Pages\ListCarePlans::class)
            ->callTableAction('publish', $plan);

        $this->assertSame('published', $plan->fresh()->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.published',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
            'from_status' => 'draft',
            'to_status' => 'published',
        ]);
    }

    public function test_manager_can_delete_draft_care_plan(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-301');

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => '2026-11-12',
            'action' => 'Aksi dibatalkan',
            'status' => 'draft',
        ]);

        Livewire::test(\App\Filament\Manager\Resources\CarePlans\Pages\ListCarePlans::class)
            ->callTableAction('delete_draft', $plan);

        $this->assertDatabaseMissing('care_plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_action_group_and_delete_actions_are_hidden_for_published_care_plan(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-302');

        $publishedPlan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => '2026-11-15',
            'action' => 'Perawatan pohon terbit',
            'status' => 'published',
        ]);

        Livewire::test(\App\Filament\Manager\Resources\CarePlans\Pages\ListCarePlans::class)
            ->assertTableActionVisible('view', $publishedPlan)
            ->assertTableActionVisible('edit', $publishedPlan)
            ->assertTableActionHidden('edit_draft', $publishedPlan)
            ->assertTableActionHidden('publish', $publishedPlan)
            ->assertTableActionHidden('delete_draft', $publishedPlan)
            ->assertTableActionHidden('delete_more', $publishedPlan);
    }
}

