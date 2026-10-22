<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class DemoWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new LogicException('DemoWorkspaceSeeder hanya boleh dijalankan pada lingkungan lokal atau pengujian.');
        }

        $password = (string) env('DEMO_PASSWORD', 'demo-hijauku');

        $users = collect([
            ['name' => 'Pengelola Demo', 'email' => 'pengelola@hijauku.test', 'role' => UserRole::Manager],
            ['name' => 'Petugas Demo', 'email' => 'petugas@hijauku.test', 'role' => UserRole::FieldStaff],
            ['name' => 'Warga Demo', 'email' => 'warga@hijauku.test', 'role' => UserRole::Citizen],
        ])->mapWithKeys(function (array $account) use ($password): array {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($password),
                    'role' => $account['role']->value,
                    'is_active' => true,
                ],
            );

            return [$account['role']->value => $user];
        });

        $program = Program::query()->updateOrCreate(
            ['name' => 'Program Percontohan Hijauku'],
            [
                'objective' => 'Data contoh untuk memeriksa alur dasar aplikasi.',
                'starts_on' => now()->toDateString(),
                'ends_on' => now()->addMonths(6)->toDateString(),
                'status' => 'active',
            ],
        );

        $site = Site::query()->updateOrCreate(
            ['program_id' => $program->id, 'name' => 'Lokasi Percontohan'],
            ['address' => 'Alamat demo — ganti dengan lokasi yang sudah divalidasi tim.'],
        );

        $plot = Plot::query()->updateOrCreate(
            ['site_id' => $site->id, 'code' => 'PETAK-DEMO'],
            ['capacity' => 50, 'status' => 'open'],
        );

        $species = TreeSpecies::query()->updateOrCreate(
            ['name' => 'Jenis Pohon Demo'],
            ['care_notes' => 'Catatan perawatan contoh; sesuaikan dengan sumber yang tervalidasi.', 'is_active' => true],
        );

        $batch = PlantingBatch::query()->updateOrCreate(
            ['plot_id' => $plot->id, 'code' => 'TANAM-DEMO-001'],
            ['planted_on' => now()->subMonths(2)->toDateString(), 'notes' => 'Kelompok penanaman contoh.'],
        );

        Tree::query()->updateOrCreate(
            ['code' => 'POHON-DEMO-001'],
            [
                'species_id' => $species->id,
                'plot_id' => $plot->id,
                'batch_id' => $batch->id,
                'planted_on' => $batch->planted_on,
                'initial_condition' => 'healthy',
            ],
        );

        Activity::query()->updateOrCreate(
            ['site_id' => $site->id, 'title' => 'Kegiatan Perawatan Demo'],
            [
                'kind' => 'care',
                'starts_at' => now()->addWeek(),
                'ends_at' => now()->addWeek()->addHours(2),
                'quota' => 20,
                'status' => 'open',
            ],
        );

        $this->command?->info('Akun demo: pengelola@hijauku.test, petugas@hijauku.test, warga@hijauku.test');
        $this->command?->info('Kata sandi demo diambil dari DEMO_PASSWORD pada .env lokal.');
    }
}
