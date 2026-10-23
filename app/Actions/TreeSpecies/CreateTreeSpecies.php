<?php

namespace App\Actions\TreeSpecies;

use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateTreeSpecies
{
    /**
     * Create a new tree species and its initial audit event.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): TreeSpecies
    {
        Gate::forUser($actor)->authorize('create', TreeSpecies::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'name',
            'care_notes',
            'is_active',
        ]));

        if (isset($attributes['name']) && is_string($attributes['name'])) {
            $attributes['name'] = trim($attributes['name']);
        }

        if (isset($attributes['care_notes']) && is_string($attributes['care_notes'])) {
            $attributes['care_notes'] = trim($attributes['care_notes']);
            if ($attributes['care_notes'] === '') {
                $attributes['care_notes'] = null;
            }
        }

        if (! array_key_exists('is_active', $attributes) || $attributes['is_active'] === null) {
            $attributes['is_active'] = true;
        }

        $data = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255', 'unique:tree_species,name'],
            'care_notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama jenis pohon wajib diisi.',
            'name.unique' => 'Nama jenis pohon sudah terdaftar.',
            'name.max' => 'Nama jenis pohon maksimal 255 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $data): TreeSpecies {
            $species = new TreeSpecies($data);
            $species->save();

            $species->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'tree_species.created',
                'to_status' => $species->is_active ? 'active' : 'inactive',
                'metadata' => [
                    'name' => $species->name,
                ],
            ]);

            return $species;
        });
    }
}
