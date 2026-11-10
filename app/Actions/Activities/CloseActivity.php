<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CloseActivity
{
    /**
     * Complete/close an activity (must be open or draft, not already completed or cancelled) and record the audit log with a reason.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Activity $activity, array $attributes): Activity
    {
        Gate::forUser($actor)->authorize('close', $activity);

        if ($activity->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'Kegiatan ini sudah selesai sebelumnya.',
            ]);
        }

        if ($activity->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Kegiatan yang sudah dibatalkan tidak dapat ditutup.',
            ]);
        }

        // Kriteria 3: Penutupan/batal tercatat dengan alasan
        $reason = isset($attributes['reason']) && is_string($attributes['reason'])
            ? trim($attributes['reason'])
            : '';

        $validated = Validator::make(['reason' => $reason], [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Catatan/alasan penutupan kegiatan wajib diisi.',
            'reason.min' => 'Catatan/alasan penutupan kegiatan minimal 5 karakter.',
            'reason.max' => 'Catatan/alasan penutupan kegiatan tidak boleh melebihi 500 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $activity, $validated): Activity {
            $fromStatus = $activity->status;

            $activity->forceFill([
                'status' => 'completed',
            ]);
            $activity->save();

            // Kriteria 3: Penutupan tercatat dengan alasan
            $activity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity.completed',
                'from_status' => $fromStatus,
                'to_status' => 'completed',
                'metadata' => [
                    'reason' => $validated['reason'],
                    'registrations_count' => $activity->registrations()->count(),
                ],
            ]);

            return $activity;
        });
    }
}

