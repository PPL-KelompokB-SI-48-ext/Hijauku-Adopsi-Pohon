<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateActivityQuota
{
    /**
     * Update an activity's quota with confirmation check if participants already registered.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Activity $activity, array $attributes): Activity
    {
        Gate::forUser($actor)->authorize('updateQuota', $activity);

        $rawQuota = $attributes['quota'] ?? null;
        if ($rawQuota === '' || $rawQuota === null) {
            $rawQuota = null;
        }

        $validated = Validator::make(['quota' => $rawQuota], [
            'quota' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ], [
            'quota.integer' => 'Kuota berupa bilangan positif bila diisi.',
            'quota.min' => 'Kuota berupa bilangan positif bila diisi.',
            'quota.max' => 'Kuota peserta tidak boleh melebihi 65535.',
        ])->validate();

        $newQuota = $validated['quota'];
        $registeredCount = $activity->registrations()->where('status', '!=', 'cancelled')->count();

        if ($registeredCount > 0 && (int) $newQuota !== (int) $activity->quota) {
            $confirmed = ! empty($attributes['confirm_quota_change']);

            if (! $confirmed) {
                throw ValidationException::withMessages([
                    'quota' => 'Kuota tidak bisa diubah setelah ada pendaftar tanpa konfirmasi.',
                ]);
            }

            if ($newQuota !== null && $newQuota < $registeredCount) {
                throw ValidationException::withMessages([
                    'quota' => "Kuota baru ({$newQuota}) tidak boleh lebih kecil dari jumlah pendaftar yang sudah ada ({$registeredCount} peserta).",
                ]);
            }
        }

        return DB::transaction(function () use ($actor, $activity, $newQuota, $registeredCount, $attributes): Activity {
            $oldQuota = $activity->quota;

            $activity->forceFill([
                'quota' => $newQuota,
            ]);
            $activity->save();

            $activity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity.quota_updated',
                'from_status' => $activity->status,
                'to_status' => $activity->status,
                'metadata' => [
                    'old_quota' => $oldQuota,
                    'new_quota' => $newQuota,
                    'registrations_count' => $registeredCount,
                    'confirmed_change' => ! empty($attributes['confirm_quota_change']),
                ],
            ]);

            return $activity;
        });
    }
}

