<?php

namespace Tests\Feature;

use App\Actions\TreeIssues\CreateTreeIssue;
use App\Enums\UserRole;
use App\Filament\Citizen\Resources\TreeIssues\Pages\CreateTreeIssue as CitizenCreateTreeIssue;
use App\Filament\Citizen\Resources\TreeIssues\Pages\ListTreeIssues as CitizenListTreeIssues;
use App\Filament\FieldStaff\Resources\TreeIssues\Pages\ListTreeIssues as FieldStaffListTreeIssues;
use App\Models\Activity;
use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\FieldAssignment;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\TreeIssue;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TreeIssueCreationTest extends TestCase
{
    use RefreshDatabase;

    private int $treeSequence = 0;

    public function test_citizen_can_report_an_active_adopted_tree_with_private_evidence(): void
    {
        Storage::fake('private');

        $citizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($citizen, $tree);

        $issue = app(CreateTreeIssue::class)->handle($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => '  Ada cabang yang patah setelah hujan.  ',
            'severity' => 'high',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
            'reporter_id' => $this->fieldStaff()->getKey(),
            'status' => 'resolved',
            'reviewer_id' => $this->manager()->getKey(),
        ]);

        $this->assertModelExists($issue);
        $this->assertDatabaseHas('tree_issues', [
            'id' => $issue->getKey(),
            'tree_id' => $tree->getKey(),
            'reporter_id' => $citizen->getKey(),
            'description' => 'Ada cabang yang patah setelah hujan.',
            'severity' => 'high',
            'status' => 'submitted',
            'reviewer_id' => null,
        ]);
        $this->assertStringStartsWith('tree-issues/evidence/', $issue->evidence_path);
        Storage::disk('private')->assertExists($issue->evidence_path);
    }

    public function test_citizen_cannot_report_a_tree_adopted_by_someone_else(): void
    {
        Storage::fake('private');

        $citizen = $this->citizen();
        $otherCitizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($otherCitizen, $tree);

        $this->assertValidationError($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => 'Pohon menunjukkan tanda kerusakan.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ], 'tree_id');

        $this->assertDatabaseCount('tree_issues', 0);
    }

    public function test_citizen_cannot_report_a_tree_without_an_active_adoption(): void
    {
        $citizen = $this->citizen();

        $this->assertValidationError($citizen, [
            'tree_id' => $this->tree()->getKey(),
            'description' => 'Pohon menunjukkan tanda kerusakan.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ], 'tree_id');

        $this->assertDatabaseCount('tree_issues', 0);
    }

    public function test_field_staff_can_report_a_tree_assigned_to_them(): void
    {
        Storage::fake('private');

        $fieldStaff = $this->fieldStaff();
        $tree = $this->tree();
        $this->assignTree($fieldStaff, $tree);

        $issue = app(CreateTreeIssue::class)->handle($fieldStaff, [
            'tree_id' => $tree->getKey(),
            'description' => 'Daun pohon tampak menguning.',
            'severity' => 'urgent',
            'evidence' => UploadedFile::fake()->create('bukti.webp', 100, 'image/webp'),
        ]);

        $this->assertDatabaseHas('tree_issues', [
            'id' => $issue->getKey(),
            'reporter_id' => $fieldStaff->getKey(),
            'tree_id' => $tree->getKey(),
            'severity' => 'urgent',
            'status' => 'submitted',
        ]);
        Storage::disk('private')->assertExists($issue->evidence_path);
    }

    public function test_field_staff_cannot_report_a_tree_that_is_not_assigned_to_them(): void
    {
        $fieldStaff = $this->fieldStaff();

        $this->assertValidationError($fieldStaff, [
            'tree_id' => $this->tree()->getKey(),
            'description' => 'Daun pohon tampak menguning.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create('bukti.png', 100, 'image/png'),
        ], 'tree_id');

        $this->assertDatabaseCount('tree_issues', 0);
    }

    public function test_manager_cannot_submit_a_tree_issue(): void
    {
        $this->expectException(AuthorizationException::class);

        app(CreateTreeIssue::class)->handle($this->manager(), []);
    }

    public function test_tree_issue_form_requires_tree_description_severity_and_evidence(): void
    {
        $citizen = $this->citizen();
        $this->actingAs($citizen);
        Filament::setCurrentPanel('warga');

        Livewire::test(CitizenCreateTreeIssue::class)
            ->call('create')
            ->assertHasFormErrors([
                'tree_id' => 'required',
                'description' => 'required',
                'severity' => 'required',
                'evidence' => 'required',
            ]);

        $this->assertDatabaseCount('tree_issues', 0);
    }

    public function test_tree_issue_rejects_an_unsupported_severity(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($citizen, $tree);

        $this->assertValidationError($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => 'Pohon menunjukkan tanda kerusakan.',
            'severity' => 'emergency',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ], 'severity');
    }

    #[DataProvider('photoFormats')]
    public function test_tree_issue_accepts_each_supported_photo_format(string $filename, string $mimeType): void
    {
        Storage::fake('private');

        $citizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($citizen, $tree);

        $issue = app(CreateTreeIssue::class)->handle($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => 'Pohon perlu diperiksa.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create($filename, 100, $mimeType),
        ]);

        Storage::disk('private')->assertExists($issue->evidence_path);
    }

    public static function photoFormats(): array
    {
        return [
            'JPG' => ['bukti.jpg', 'image/jpeg'],
            'PNG' => ['bukti.png', 'image/png'],
            'WebP' => ['bukti.webp', 'image/webp'],
        ];
    }

    public function test_tree_issue_rejects_photo_larger_than_five_megabytes(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($citizen, $tree);

        $this->assertValidationError($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => 'Pohon perlu diperiksa.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 5121, 'image/jpeg'),
        ], 'evidence');

        $this->assertDatabaseCount('tree_issues', 0);
    }

    public function test_tree_issue_accepts_photo_at_the_five_megabyte_limit(): void
    {
        Storage::fake('private');

        $citizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($citizen, $tree);

        $issue = app(CreateTreeIssue::class)->handle($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => 'Pohon perlu diperiksa.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 5120, 'image/jpeg'),
        ]);

        Storage::disk('private')->assertExists($issue->evidence_path);
    }

    public function test_tree_issue_rejects_non_image_evidence(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();
        $this->adoptTree($citizen, $tree);

        $this->assertValidationError($citizen, [
            'tree_id' => $tree->getKey(),
            'description' => 'Pohon perlu diperiksa.',
            'severity' => 'normal',
            'evidence' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ], 'evidence');
    }

    public function test_reporter_can_download_their_evidence_but_another_user_cannot(): void
    {
        Storage::fake('private');

        $citizen = $this->citizen();
        $otherCitizen = $this->citizen();
        $evidencePath = 'tree-issues/evidence/private-proof.jpg';
        Storage::disk('private')->put($evidencePath, 'private image content');
        $issue = TreeIssue::query()->create([
            'tree_id' => $this->tree()->getKey(),
            'reporter_id' => $citizen->getKey(),
            'description' => 'Bukti laporan.',
            'severity' => 'normal',
            'evidence_path' => $evidencePath,
            'status' => 'submitted',
        ]);

        $this->actingAs($citizen)
            ->get(route('tree-issues.evidence', $issue))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($otherCitizen)
            ->get(route('tree-issues.evidence', $issue))
            ->assertNotFound();
    }

    public function test_citizen_list_only_displays_their_own_reports(): void
    {
        $citizen = $this->citizen();
        $otherCitizen = $this->citizen();
        $ownIssue = $this->issue($citizen, $this->tree());
        $otherIssue = $this->issue($otherCitizen, $this->tree());

        $this->actingAs($citizen);
        Filament::setCurrentPanel('warga');

        Livewire::test(CitizenListTreeIssues::class)
            ->assertCanSeeTableRecords([$ownIssue])
            ->assertCanNotSeeTableRecords([$otherIssue]);
    }

    public function test_field_staff_list_only_displays_their_own_reports(): void
    {
        $fieldStaff = $this->fieldStaff();
        $otherFieldStaff = $this->fieldStaff();
        $ownIssue = $this->issue($fieldStaff, $this->tree());
        $otherIssue = $this->issue($otherFieldStaff, $this->tree());

        $this->actingAs($fieldStaff);
        Filament::setCurrentPanel('petugas');

        Livewire::test(FieldStaffListTreeIssues::class)
            ->assertCanSeeTableRecords([$ownIssue])
            ->assertCanNotSeeTableRecords([$otherIssue]);
    }

    private function assertValidationError(User $actor, array $attributes, string $field): void
    {
        try {
            app(CreateTreeIssue::class)->handle($actor, $attributes);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());

            return;
        }

        $this->fail("Input yang tidak valid pada {$field} seharusnya ditolak.");
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
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

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function tree(): Tree
    {
        $suffix = (string) ++$this->treeSequence;
        $manager = $this->manager();
        $program = Program::query()->create([
            'name' => 'Program Tes Laporan '.$suffix,
            'objective' => 'Pengujian laporan masalah pohon.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);
        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Lokasi Tes Laporan',
            'address' => 'Lokasi pengujian.',
        ]);
        $plot = Plot::query()->create([
            'site_id' => $site->getKey(),
            'code' => 'PETAK-'.$suffix,
            'capacity' => 10,
            'status' => 'open',
        ]);
        $species = TreeSpecies::query()->create([
            'name' => 'Jenis Tes '.$suffix,
            'is_active' => true,
        ]);

        return Tree::query()->create([
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
            'code' => 'POHON-'.$suffix,
            'initial_condition' => 'healthy',
        ]);
    }

    private function adoptTree(User $citizen, Tree $tree): void
    {
        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->getKey(),
            'requester_id' => $citizen->getKey(),
            'reason' => 'Saya akan merawat pohon ini.',
            'status' => 'approved',
        ]);
        $agreement = AdoptionAgreement::query()->create([
            'request_id' => $request->getKey(),
            'tree_id' => $tree->getKey(),
            'adopter_id' => $citizen->getKey(),
            'terms' => 'Rawat pohon sesuai kebutuhan.',
            'starts_on' => '2026-11-20',
            'ends_on' => '2027-11-20',
            'status' => 'active',
        ]);
        TreeAdoptionAllocation::query()->create([
            'tree_id' => $tree->getKey(),
            'request_id' => $request->getKey(),
            'agreement_id' => $agreement->getKey(),
            'status' => 'active',
            'reserved_at' => now(),
        ]);
    }

    private function assignTree(User $fieldStaff, Tree $tree): void
    {
        $activity = Activity::query()->create([
            'site_id' => $tree->plot->site_id,
            'title' => 'Penugasan Tes Laporan',
            'kind' => 'care',
            'starts_at' => '2026-11-20 08:00:00',
            'ends_at' => '2026-11-20 12:00:00',
            'quota' => 10,
            'status' => 'open',
        ]);

        FieldAssignment::query()->create([
            'activity_id' => $activity->getKey(),
            'tree_id' => $tree->getKey(),
            'assignee_id' => $fieldStaff->getKey(),
            'status' => 'assigned',
        ]);
    }

    private function issue(User $reporter, Tree $tree): TreeIssue
    {
        return TreeIssue::query()->create([
            'tree_id' => $tree->getKey(),
            'reporter_id' => $reporter->getKey(),
            'description' => 'Pohon perlu diperiksa.',
            'severity' => 'normal',
            'evidence_path' => 'tree-issues/evidence/example.jpg',
            'status' => 'submitted',
        ]);
    }
}
