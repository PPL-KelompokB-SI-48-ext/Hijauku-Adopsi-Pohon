<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CancelActivity
{
    /**
     * Cancel an activity before it is completed, updating participants' status accordingly and recording the audit log with a reason.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Activity $activity, array $attributes): Activity
    {
        Gate::forUser($actor)->authorize('cancel', $activity);

        // Kriteria 1: Pembatalan hanya sebelum kegiatan selesai
        if ($activity->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'Kegiatan yang sudah selesai tidak dapat dibatalkan.',
            ]);
        }

        if ($activity->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Kegiatan ini sudah dibatalkan sebelumnya.',
            ]);
        }

        // Kriteria 3: Penutupan/batal tercatat dengan alasan
        $reason = isset($attributes['reason']) && is_string($attributes['reason'])
            ? trim($attributes['reason'])
            : '';

        $validated = Validator::make(['reason' => $reason], [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan kegiatan wajib diisi.',
            'reason.min' => 'Alasan pembatalan kegiatan minimal 5 karakter.',
            'reason.max' => 'Alasan pembatalan kegiatan tidak boleh melebihi 500 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $activity, $validated): Activity {
            $fromStatus = $activity->status;

            // Kriteria 2: Peserta yang sudah daftar mendapat status yang tepat (cancelled)
            $affectedRegistrations = $activity->registrations()
                ->where('status', '!=', 'cancelled')
                ->update(['status' => 'cancelled']);

            $activity->forceFill([
                'status' => 'cancelled',
            ]);
            $activity->save();

            // Kriteria 3: Pembatalan tercatat dengan alasan
            $activity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity.cancelled',
                'from_status' => $fromStatus,
                'to_status' => 'cancelled',
                'metadata' => [
                    'reason' => $validated['reason'],
                    'affected_registrations' => $affectedRegistrations,
                ],
            ]);

            return $activity;
        });
    }
}

