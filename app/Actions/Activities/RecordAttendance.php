<?php

namespace App\Actions\Activities;

use App\Models\ActivityRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RecordAttendance
{
    /**
     * Record participant attendance for an activity.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, ActivityRegistration $registration, array $attributes = []): ActivityRegistration
    {
        Gate::forUser($actor)->authorize('recordAttendance', $registration);

        // Kriteria 1: Hanya pendaftar yang bisa ditandai hadir
        if ($registration->status === 'cancelled') {
            throw ValidationException::withMessages([
                'registration' => 'Pendaftaran yang telah dibatalkan tidak dapat ditandai hadir.',
            ]);
        }

        if ($registration->status === 'attended') {
            throw ValidationException::withMessages([
                'registration' => 'Peserta sudah ditandai hadir sebelumnya.',
            ]);
        }

        if ($registration->status !== 'registered') {
            throw ValidationException::withMessages([
                'registration' => 'Hanya pendaftar berstatus terdaftar yang dapat ditandai hadir.',
            ]);
        }

        // Pastikan kegiatan tidak dibatalkan
        $activity = $registration->activity;
        if ($activity && $activity->status === 'cancelled') {
            throw ValidationException::withMessages([
                'activity' => 'Kehadiran tidak dapat dicatat untuk kegiatan yang telah dibatalkan.',
            ]);
        }

        $validated = Validator::make($attributes, [
            'attended_at' => ['nullable', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'attended_at.before_or_equal' => 'Waktu kehadiran tidak boleh melebihi waktu sekarang.',
            'notes.max' => 'Catatan tidak boleh melebihi 500 karakter.',
        ])->validate();

        $attendedAt = ! empty($validated['attended_at'])
            ? Carbon::parse($validated['attended_at'])
            : now();

        return DB::transaction(function () use ($actor, $registration, $attendedAt, $validated): ActivityRegistration {
            $fromStatus = $registration->status;

            // Kriteria 2: Waktu kehadiran tercatat
            $registration->forceFill([
                'status' => 'attended',
                'attended_at' => $attendedAt,
            ]);
            $registration->save();

            // Jejak perubahan dicatat ke activity_logs
            $activity = $registration->activity;
            if ($activity) {
                $activity->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'activity_registration.attendance_recorded',
                    'from_status' => $fromStatus,
                    'to_status' => 'attended',
                    'metadata' => array_filter([
                        'registration_id' => $registration->getKey(),
                        'user_id' => $registration->user_id,
                        'user_name' => $registration->user?->name,
                        'attended_at' => $attendedAt->toIso8601String(),
                        'notes' => $validated['notes'] ?? null,
                    ]),
                ]);
            }

            return $registration;
        });
    }
}
