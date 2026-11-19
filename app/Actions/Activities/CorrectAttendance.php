<?php

namespace App\Actions\Activities;

use App\Models\ActivityRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CorrectAttendance
{
    /**
     * Correct participant attendance status or timestamp with explicit reason and audit log.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, ActivityRegistration $registration, array $attributes): ActivityRegistration
    {
        Gate::forUser($actor)->authorize('correctAttendance', $registration);

        // Kriteria 3: Status kehadiran bisa dikoreksi
        if ($registration->status !== 'attended') {
            throw ValidationException::withMessages([
                'registration' => 'Hanya pendaftaran yang berstatus hadir yang dapat dikoreksi kehadirannya.',
            ]);
        }

        $validated = Validator::make($attributes, [
            'status' => ['required', 'string', 'in:registered,attended'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'attended_at' => ['nullable', 'date', 'before_or_equal:now', 'required_if:status,attended'],
        ], [
            'status.required' => 'Status koreksi kehadiran wajib dipilih.',
            'status.in' => 'Status koreksi tidak valid.',
            'reason.required' => 'Alasan koreksi kehadiran wajib diisi.',
            'reason.min' => 'Alasan koreksi kehadiran minimal 5 karakter.',
            'reason.max' => 'Alasan koreksi kehadiran tidak boleh melebihi 500 karakter.',
            'attended_at.required_if' => 'Waktu kehadiran wajib diisi jika status tetap hadir.',
            'attended_at.before_or_equal' => 'Waktu kehadiran tidak boleh melebihi waktu sekarang.',
        ])->validate();

        $fromStatus = $registration->status;
        $toStatus = $validated['status'];
        $previousAttendedAt = $registration->attended_at;

        $newAttendedAt = null;
        if ($toStatus === 'attended') {
            $newAttendedAt = ! empty($validated['attended_at'])
                ? Carbon::parse($validated['attended_at'])
                : $previousAttendedAt;
        }

        return DB::transaction(function () use ($actor, $registration, $fromStatus, $toStatus, $previousAttendedAt, $newAttendedAt, $validated): ActivityRegistration {
            $registration->forceFill([
                'status' => $toStatus,
                'attended_at' => $newAttendedAt,
            ]);
            $registration->save();

            // Jejak perubahan dicatat ke activity_logs
            $activity = $registration->activity;
            if ($activity) {
                $activity->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'activity_registration.attendance_corrected',
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'metadata' => array_filter([
                        'registration_id' => $registration->getKey(),
                        'user_id' => $registration->user_id,
                        'user_name' => $registration->user?->name,
                        'reason' => $validated['reason'],
                        'previous_attended_at' => $previousAttendedAt?->toIso8601String(),
                        'new_attended_at' => $newAttendedAt?->toIso8601String(),
                    ]),
                ]);
            }

            return $registration;
        });
    }
}
