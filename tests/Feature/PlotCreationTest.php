<?php

namespace Tests\Feature;

use App\Actions\Plots\CreatePlot;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Plots\Pages\CreatePlot as CreatePlotPage;
use App\Filament\Manager\Resources\Plots\Pages\ListPlots;
use App\Models\Plot;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PlotCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_plot_with_positive_capacity_for_a_site(): void
    {
        $manager = $this->manager();
        $site = Site::factory()->create();
        $this->openManagerPanelAs($manager);

        Livewire::test(CreatePlotPage::class)
            ->fillForm([
                'site_id' => $site->getKey(),
                'code' => '  A-01  ',
                'capacity' => 30,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('plots', [
            'site_id' => $site->getKey(),
            'code' => 'A-01',
            'capacity' => 30,
            'status' => 'open',
        ]);

        $plot = Plot::query()->where('site_id', $site->getKey())->sole();

        Livewire::test(ListPlots::class)
            ->assertSee('Atur Ulang')
            ->assertCanSeeTableRecords([$plot]);
    }

    public function test_plot_code_must_be_unique_within_its_site_but_can_repeat_at_another_site(): void
    {
        $manager = $this->manager();
        $site = Site::factory()->create();
        $otherSite = Site::factory()->create();
        $action = app(CreatePlot::class);

        $action->handle($manager, [
            'site_id' => $site->getKey(),
            'code' => 'A-01',
            'capacity' => 10,
        ]);

        try {
            $action->handle($manager, [
                'site_id' => $site->getKey(),
                'code' => 'A-01',
                'capacity' => 20,
            ]);

            $this->fail('Kode yang sudah dipakai pada lokasi yang sama harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('code', $exception->errors());
        }

        $otherPlot = $action->handle($manager, [
            'site_id' => $otherSite->getKey(),
            'code' => 'A-01',
            'capacity' => 20,
        ]);

        $this->assertSame($otherSite->getKey(), $otherPlot->site_id);
        $this->assertDatabaseCount('plots', 2);
    }

    public function test_plot_code_is_normalized_to_uppercase_before_saving(): void
    {
        $manager = $this->manager();
        $site = Site::factory()->create();

        $plot = app(CreatePlot::class)->handle($manager, [
            'site_id' => $site->getKey(),
            'code' => '  a-01  ',
            'capacity' => 10,
        ]);

        $this->assertSame('A-01', $plot->code);
        $this->assertDatabaseHas('plots', [
            'site_id' => $site->getKey(),
            'code' => 'A-01',
        ]);
    }

    public function test_form_rejects_missing_location_code_and_non_positive_capacity(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        Livewire::test(CreatePlotPage::class)
            ->fillForm([
                'site_id' => null,
                'code' => '   ',
                'capacity' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'site_id' => 'required',
                'code' => 'required',
                'capacity' => 'min',
            ]);

        $this->assertDatabaseCount('plots', 0);
    }

    public function test_create_form_reports_a_duplicate_code_for_the_selected_site(): void
    {
        $manager = $this->manager();
        $site = Site::factory()->create();
        $site->plots()->create(['code' => 'A-01', 'capacity' => 10]);
        $this->openManagerPanelAs($manager);

        Livewire::test(CreatePlotPage::class)
            ->fillForm([
                'site_id' => $site->getKey(),
                'code' => 'A-01',
                'capacity' => 20,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'code' => 'unique',
            ])
            ->assertSee('Kode petak sudah digunakan pada lokasi ini.');

        $this->assertDatabaseCount('plots', 1);
    }

    public function test_action_rejects_unknown_site_fractional_capacity_and_capacity_above_schema_limit(): void
    {
        $manager = $this->manager();
        $site = Site::factory()->create();
        $action = app(CreatePlot::class);

        foreach ([
            ['site_id' => 999999, 'code' => 'X-01', 'capacity' => 10, 'field' => 'site_id'],
            ['site_id' => $site->getKey(), 'code' => 'X-02', 'capacity' => 1.5, 'field' => 'capacity'],
            ['site_id' => $site->getKey(), 'code' => 'X-03', 'capacity' => 65536, 'field' => 'capacity'],
        ] as $case) {
            try {
                $action->handle($manager, [
                    'site_id' => $case['site_id'],
                    'code' => $case['code'],
                    'capacity' => $case['capacity'],
                ]);

                $this->fail('Input tidak valid seharusnya ditolak.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($case['field'], $exception->errors());
            }
        }

        $this->assertDatabaseCount('plots', 0);
    }

    public function test_database_unique_index_rejects_duplicate_plot_code_for_same_site(): void
    {
        $site = Site::factory()->create();
        $site->plots()->create(['code' => 'A-01', 'capacity' => 10]);

        $this->expectException(UniqueConstraintViolationException::class);

        $site->plots()->create(['code' => 'A-01', 'capacity' => 20]);
    }

    public function test_only_managers_can_open_or_create_plots(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
        $site = Site::factory()->create();

        $this->actingAs($fieldStaff)
            ->get('/app/plots')
            ->assertForbidden();

        $this->assertFalse($fieldStaff->can('viewAny', Plot::class));

        $this->expectException(AuthorizationException::class);

        app(CreatePlot::class)->handle($fieldStaff, [
            'site_id' => $site->getKey(),
            'code' => 'A-01',
            'capacity' => 10,
        ]);
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
