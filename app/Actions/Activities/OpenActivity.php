<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OpenActivity
{
    /**
     * Open a draft activity with an optional quota limit and record the audit event.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Activity $activity, array $attributes = []): Activity
    {
        Gate::forUser($actor)->authorize('open', $activity);

        if ($activity->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Hanya kegiatan berstatus draf yang bisa dibuka.',
            ]);
        }

        // Determine new quota if passed, otherwise keep current quota.
        $hasQuotaKey = array_key_exists('quota', $attributes);
        $rawQuota = $hasQuotaKey ? $attributes['quota'] : $activity->quota;
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

        // Check if quota is being changed after registrations exist.
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
            $fromStatus = $activity->status;
            $oldQuota = $activity->quota;

            $activity->forceFill([
                'status' => 'open',
                'quota' => $newQuota,
            ]);
            $activity->save();

            $activity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity.opened',
                'from_status' => $fromStatus,
                'to_status' => 'open',
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

