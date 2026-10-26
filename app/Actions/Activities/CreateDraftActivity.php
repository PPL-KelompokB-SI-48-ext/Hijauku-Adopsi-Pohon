<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateDraftActivity
{
    /**
     * Create a draft activity and its initial audit event.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Activity
    {
        Gate::forUser($actor)->authorize('create', Activity::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'title',
            'site_id',
            'kind',
            'starts_at',
            'ends_at',
            'quota',
        ]));

        foreach (['title', 'kind'] as $field) {
            if (isset($attributes[$field]) && is_string($attributes[$field])) {
                $attributes[$field] = trim($attributes[$field]);
            }
        }

        if (array_key_exists('quota', $attributes) && ($attributes['quota'] === '' || $attributes['quota'] === null)) {
            $attributes['quota'] = null;
        }

        $data = Validator::make($attributes, [
            'title' => ['required', 'string', 'max:255'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'kind' => ['required', 'string', 'max:32'],
            'starts_at' => ['bail', 'required', 'date'],
            'ends_at' => ['bail', 'required', 'date', 'after:starts_at'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ], [
            'title.required' => 'Judul kegiatan wajib diisi.',
            'site_id.required' => 'Kegiatan wajib terikat pada satu lokasi.',
            'site_id.exists' => 'Lokasi yang dipilih tidak valid.',
            'kind.required' => 'Jenis kegiatan wajib dipilih.',
            'starts_at.required' => 'Waktu mulai kegiatan wajib diisi.',
            'starts_at.date' => 'Waktu mulai harus berupa tanggal dan waktu yang valid.',
            'ends_at.required' => 'Waktu selesai kegiatan wajib diisi.',
            'ends_at.date' => 'Waktu selesai harus berupa tanggal dan waktu yang valid.',
            'ends_at.after' => 'Waktu akhir harus setelah waktu awal.',
            'quota.integer' => 'Kuota berupa bilangan positif bila diisi.',
            'quota.min' => 'Kuota berupa bilangan positif bila diisi.',
            'quota.max' => 'Kuota peserta tidak boleh melebihi 65535.',
        ])->validate();

        return DB::transaction(function () use ($actor, $data): Activity {
            $activity = new Activity($data);
            $activity->forceFill([
                'status' => 'draft',
            ]);
            $activity->save();

            $activity->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'activity.created',
                'to_status' => 'draft',
            ]);

            return $activity;
        });
    }
}

