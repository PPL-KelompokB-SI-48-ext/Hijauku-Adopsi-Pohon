<?php

namespace App\Actions\Sites;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateSite
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Site $site, array $attributes): Site
    {
        Gate::forUser($actor)->authorize('update', $site);

        $attributes = array_intersect_key($attributes, array_flip([
            'program_id',
            'name',
            'address',
            'latitude',
            'longitude',
            'reason',
        ]));

        foreach (['name', 'address', 'reason'] as $field) {
            if (isset($attributes[$field]) && is_string($attributes[$field])) {
                $attributes[$field] = trim($attributes[$field]);
            }
        }

        $data = Validator::make($attributes, [
            'program_id' => ['bail', 'required', 'integer', 'exists:programs,id'],
            'name' => ['bail', 'required', 'string', 'max:255'],
            'address' => ['bail', 'required', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'decimal:0,7'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'decimal:0,7'],
            'reason' => ['bail', 'required', 'string', 'min:3', 'max:1000'],
        ], [
            'program_id.required' => 'Pilih program induk lokasi.',
            'program_id.exists' => 'Program induk yang dipilih tidak tersedia.',
            'name.required' => 'Nama lokasi wajib diisi.',
            'address.required' => 'Alamat lokasi wajib diisi.',
            'latitude.between' => 'Latitude harus berada di antara -90 dan 90.',
            'latitude.decimal' => 'Latitude maksimal memiliki 7 angka desimal.',
            'longitude.between' => 'Longitude harus berada di antara -180 dan 180.',
            'longitude.decimal' => 'Longitude maksimal memiliki 7 angka desimal.',
            'reason.required' => 'Alasan perubahan wajib diisi.',
            'reason.min' => 'Alasan perubahan minimal 3 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $site, $data): Site {
            $lockedSite = Site::query()
                ->lockForUpdate()
                ->findOrFail($site->getKey());

            Gate::forUser($actor)->authorize('update', $lockedSite);

            $programChanged = (int) $lockedSite->program_id !== (int) $data['program_id'];

            if ($programChanged && $lockedSite->plots()->exists()) {
                throw ValidationException::withMessages([
                    'program_id' => 'Program induk tidak dapat diubah karena lokasi sudah memiliki petak tanam.',
                ]);
            }

            $fields = ['program_id', 'name', 'address', 'latitude', 'longitude'];
            $before = array_intersect_key($lockedSite->getAttributes(), array_flip($fields));
            $lockedSite->fill(array_intersect_key($data, array_flip($fields)));

            if (! $lockedSite->isDirty($fields)) {
                return $lockedSite;
            }

            $lockedSite->save();

            $lockedSite->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'site.updated',
                'metadata' => [
                    'reason' => $data['reason'],
                    'before' => $before,
                    'after' => array_intersect_key($lockedSite->fresh()->getAttributes(), array_flip($fields)),
                ],
            ]);

            return $lockedSite->refresh();
        });
    }
}
