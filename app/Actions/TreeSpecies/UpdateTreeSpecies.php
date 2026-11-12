<?php

namespace App\Actions\TreeSpecies;

use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateTreeSpecies
{
    /**
     * Update an existing tree species and record its audit log.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, TreeSpecies $species, array $attributes): TreeSpecies
    {
        Gate::forUser($actor)->authorize('update', $species);

        $reason = null;
        if (isset($attributes['reason']) && is_string($attributes['reason'])) {
            $reason = trim($attributes['reason']);
            if ($reason === '') {
                $reason = null;
            }
        }

        $input = array_intersect_key($attributes, array_flip([
            'name',
            'care_notes',
            'is_active',
        ]));

        if (isset($input['name']) && is_string($input['name'])) {
            $input['name'] = trim($input['name']);
        }

        if (array_key_exists('care_notes', $input) && is_string($input['care_notes'])) {
            $input['care_notes'] = trim($input['care_notes']);
            if ($input['care_notes'] === '') {
                $input['care_notes'] = null;
            }
        }

        $validated = Validator::make($input, [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tree_species', 'name')->ignore($species->getKey()),
            ],
            'care_notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama jenis pohon wajib diisi.',
            'name.unique' => 'Nama jenis pohon sudah terdaftar.',
            'name.max' => 'Nama jenis pohon maksimal 255 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $species, $validated, $reason): TreeSpecies {
            $lockedSpecies = TreeSpecies::query()
                ->lockForUpdate()
                ->findOrFail($species->getKey());

            $oldAttributes = [
                'name' => $lockedSpecies->name,
                'care_notes' => $lockedSpecies->care_notes,
                'is_active' => (bool) $lockedSpecies->is_active,
            ];

            $lockedSpecies->fill($validated);
            $changes = $lockedSpecies->getDirty();

            if (! empty($changes)) {
                $lockedSpecies->save();

                $newAttributes = [
                    'name' => $lockedSpecies->name,
                    'care_notes' => $lockedSpecies->care_notes,
                    'is_active' => (bool) $lockedSpecies->is_active,
                ];

                $metadata = [
                    'old' => $oldAttributes,
                    'new' => $newAttributes,
                ];

                if ($reason !== null) {
                    $metadata['reason'] = $reason;
                }

                $lockedSpecies->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'tree_species.updated',
                    'from_status' => $oldAttributes['is_active'] ? 'active' : 'inactive',
                    'to_status' => $lockedSpecies->is_active ? 'active' : 'inactive',
                    'metadata' => $metadata,
                ]);
            }

            return $lockedSpecies;
        });
    }
}
