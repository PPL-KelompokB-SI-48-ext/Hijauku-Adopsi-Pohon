<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\ActivityRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegisterForActivity
{
    /**
     * Register a citizen for an open activity with race-safe row locking and audit logging.
     */
    public function handle(User $actor, Activity $activity): ActivityRegistration
    {
        Gate::forUser($actor)->authorize('create', ActivityRegistration::class);

        return DB::transaction(function () use ($actor, $activity): ActivityRegistration {
            // Lock the activity record for update to prevent race conditions on quota
            /** @var Activity $lockedActivity */
            $lockedActivity = Activity::query()
                ->lockForUpdate()
                ->findOrFail($activity->getKey());

            // Pastikan kegiatan berstatus open
            if ($lockedActivity->status !== 'open') {
                throw ValidationException::withMessages([
                    'activity' => 'Pendaftaran hanya dapat dilakukan pada kegiatan yang berstatus terbuka.',
                ]);
            }

            // Kriteria 1: Satu akun hanya satu pendaftaran per kegiatan
            $existing = ActivityRegistration::query()
                ->where('activity_id', $lockedActivity->getKey())
                ->where('user_id', $actor->getKey())
                ->first();

            if ($existing) {
                if ($existing->status === 'registered') {
                    throw ValidationException::withMessages([
                        'activity' => 'Anda sudah terdaftar pada kegiatan ini.',
                    ]);
                }

                // Jika pendaftaran sebelumnya dibatalkan, apakah diizinkan daftar ulang jika kuota tersedia?
                // Cek kuota sebelum mengaktifkan kembali
                if ($lockedActivity->quota !== null) {
                    $currentActiveCount = ActivityRegistration::query()
                        ->where('activity_id', $lockedActivity->getKey())
                        ->where('status', 'registered')
                        ->count();

                    if ($currentActiveCount >= $lockedActivity->quota) {
                        throw ValidationException::withMessages([
                            'quota' => 'Pendaftaran ditolak karena kuota kegiatan sudah penuh.',
                        ]);
                    }
                }

                $existing->forceFill([
                    'status' => 'registered',
                    'created_at' => now(),
                ]);
                $existing->save();

                $lockedActivity->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'activity.registered',
                    'from_status' => 'cancelled',
                    'to_status' => 'registered',
                    'metadata' => [
                        'user_id' => $actor->getKey(),
                        'user_name' => $actor->name,
                        're_registration' => true,
                    ],
                ]);

                return $existing;
            }

            // Kriteria 2: Pendaftaran ditolak bila kuota penuh (race-safe via row lock)
            if ($lockedActivity->quota !== null) {
                $currentActiveCount = ActivityRegistration::query()
                    ->where('activity_id', $lockedActivity->getKey())
                    ->where('status', 'registered')
                    ->count();

                if ($currentActiveCount >= $lockedActivity->quota) {
                    throw ValidationException::withMessages([
                        'quota' => 'Pendaftaran ditolak karena kuota kegiatan sudah penuh.',
                    ]);
                }
            }

            // Kriteria 3: Pendaftar tercatat dengan waktu
            $registration = new ActivityRegistration([
                'activity_id' => $lockedActivity->getKey(),
                'user_id' => $actor->getKey(),
                'status' => 'registered',
            ]);
            $registration->save();

            // Jejak perubahan di activity_logs
            $lockedActivity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity.registered',
                'from_status' => null,
                'to_status' => 'registered',
                'metadata' => [
                    'user_id' => $actor->getKey(),
                    'user_name' => $actor->name,
                    'registered_at' => $registration->created_at->toIso8601String(),
                ],
            ]);

            return $registration;
        });
    }
}
