<?php

namespace Tests\Browser;

use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProgramStatusWorkflowTest extends DuskTestCase
{
    public function test_manager_can_activate_a_draft_and_archive_a_completed_program_from_the_browser(): void
    {
        $manager = User::query()->where('email', 'pengelola@hijauku.test')->firstOrFail();
        $name = 'Program Status '.Str::upper(Str::random(8));
        $program = Program::query()->forceCreate([
            'name' => $name,
            'objective' => 'Menjaga pohon di lingkungan warga tetap terawat.',
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(3)->toDateString(),
            'created_by' => $manager->getKey(),
            'status' => 'draft',
        ]);
        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Lokasi '.$name,
            'address' => 'Jalan Uji Browser',
        ]);

        $this->browse(function (Browser $browser) use ($name, $program): void {
            $rowSelector = 'tr[wire\\:key$=".table.records.'.$program->getKey().'"]';

            $browser->visit('/app/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->press('Sign in')
                ->waitForLocation('/app')
                ->visit('/app/programs')
                ->assertSee($name);

            $browser->with($rowSelector, function (Browser $row): void {
                $row->press('Aktifkan');
            });

            $browser->waitForText('Alasan aktivasi')
                ->type('textarea', 'Data program sudah diverifikasi melalui browser.')
                ->press('Ya, Aktifkan')
                ->waitUntilMissingText('Alasan aktivasi', 15)
                ->assertSee($name)
                ->assertSee('Aktif');

            $this->assertSame('active', $program->fresh()->status);

            $program->forceFill(['status' => 'completed'])->save();

            $browser->refresh()->waitForText('Selesai');

            $browser->with($rowSelector, function (Browser $row): void {
                $row->press('Arsipkan');
            });

            $browser->waitForText('Alasan pengarsipan')
                ->type('textarea', 'Program selesai, lokasi tetap disimpan.')
                ->press('Ya, Arsipkan')
                ->waitUntilMissingText('Alasan pengarsipan', 15)
                ->assertSee($name)
                ->assertSee('Diarsipkan');
        });

        $this->assertSame('archived', $program->fresh()->status);
        $this->assertDatabaseHas('sites', [
            'id' => $site->getKey(),
            'program_id' => $program->getKey(),
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'program.activated',
            'subject_id' => $program->getKey(),
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'program.archived',
            'subject_id' => $program->getKey(),
        ]);
    }
}
