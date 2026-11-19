<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\ActivityRegistration;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CancelActivityRegistration
{
    /**
     * Withdraw/cancel an activity registration by a citizen safely before the activity starts.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, ActivityRegistration $registration, array $attributes = []): ActivityRegistration
    {
        Gate::forUser($actor)->authorize('cancel', $registration);

        // Kriteria 1: Hanya pendaftaran milik sendiri yang bisa ditarik
        if ((int) $registration->user_id !== (int) $actor->getKey()) {
            throw new AuthorizationException('Anda hanya dapat menarik pendaftaran milik sendiri.');
        }

        if ($registration->status === 'cancelled') {
            throw ValidationException::withMessages([
                'registration' => 'Pendaftaran ini sudah dibatalkan sebelumnya.',
            ]);
        }

        if ($registration->status === 'attended') {
            throw ValidationException::withMessages([
                'registration' => 'Pendaftaran tidak dapat ditarik karena Anda sudah tercatat hadir pada kegiatan ini.',
            ]);
        }

        if ($registration->status !== 'registered') {
            throw ValidationException::withMessages([
                'registration' => 'Hanya pendaftaran aktif yang dapat ditarik.',
            ]);
        }

        $activity = $registration->activity;
        if (! $activity) {
            throw ValidationException::withMessages([
                'activity' => 'Data kegiatan tidak ditemukan.',
            ]);
        }

        if ($activity->status === 'cancelled') {
            throw ValidationException::withMessages([
                'activity' => 'Kegiatan telah dibatalkan oleh pihak pengelola.',
            ]);
        }

        if ($activity->status === 'completed') {
            throw ValidationException::withMessages([
                'activity' => 'Kegiatan telah selesai dilaksanakan.',
            ]);
        }

        // Kriteria 2: Penarikan sebelum kegiatan dimulai
        if ($activity->starts_at->isPast() || $activity->starts_at->lte(now())) {
            throw ValidationException::withMessages([
                'activity' => 'Pendaftaran tidak dapat ditarik karena kegiatan sudah dimulai atau telah lewat.',
            ]);
        }

        $validated = Validator::make($attributes, [
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'reason.max' => 'Alasan penarikan tidak boleh melebihi 500 karakter.',
        ])->validate();

        $reason = isset($validated['reason']) && is_string($validated['reason'])
            ? trim($validated['reason'])
            : null;

        // Kriteria 3: Kuota kembali tersedia setelah penarikan (race-safe via row lock)
        return DB::transaction(function () use ($actor, $registration, $activity, $reason): ActivityRegistration {
            /** @var Activity $lockedActivity */
            $lockedActivity = Activity::query()
                ->lockForUpdate()
                ->findOrFail($activity->getKey());

            // Validasi ulang starts_at di dalam transaksi penguncian baris
            if ($lockedActivity->starts_at->lte(now())) {
                throw ValidationException::withMessages([
                    'activity' => 'Pendaftaran tidak dapat ditarik karena kegiatan sudah dimulai.',
                ]);
            }

            $fromStatus = $registration->status;

            $registration->forceFill([
                'status' => 'cancelled',
            ]);
            $registration->save();

            // Jejak perubahan dicatat ke activity_logs
            $lockedActivity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity_registration.cancelled',
                'from_status' => $fromStatus,
                'to_status' => 'cancelled',
                'metadata' => array_filter([
                    'registration_id' => $registration->getKey(),
                    'user_id' => $actor->getKey(),
                    'user_name' => $actor->name,
                    'reason' => $reason,
                ]),
            ]);

            return $registration;
        });
    }
}
