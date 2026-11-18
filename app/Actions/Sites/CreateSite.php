<?php

namespace App\Actions\Sites;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateSite
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Site
    {
        Gate::forUser($actor)->authorize('create', Site::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'program_id',
            'name',
            'address',
            'latitude',
            'longitude',
        ]));

        foreach (['name', 'address'] as $field) {
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
        ], [
            'program_id.required' => 'Pilih program induk lokasi.',
            'program_id.exists' => 'Program induk yang dipilih tidak tersedia.',
            'name.required' => 'Nama lokasi wajib diisi.',
            'address.required' => 'Alamat lokasi wajib diisi.',
            'latitude.between' => 'Latitude harus berada di antara -90 dan 90.',
            'latitude.decimal' => 'Latitude maksimal memiliki 7 angka desimal.',
            'longitude.between' => 'Longitude harus berada di antara -180 dan 180.',
            'longitude.decimal' => 'Longitude maksimal memiliki 7 angka desimal.',
        ])->validate();

        return Site::query()->create($data);
    }
}
