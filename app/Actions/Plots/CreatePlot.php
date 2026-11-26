<?php

namespace App\Actions\Plots;

use App\Models\Plot;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreatePlot
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Plot
    {
        Gate::forUser($actor)->authorize('create', Plot::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'site_id',
            'code',
            'capacity',
        ]));

        if (isset($attributes['code']) && is_string($attributes['code'])) {
            $attributes['code'] = mb_strtoupper(trim($attributes['code']));
        }

        $data = Validator::make($attributes, [
            'site_id' => ['bail', 'required', 'integer', 'exists:sites,id'],
            'code' => [
                'bail',
                'required',
                'string',
                'max:40',
                Rule::unique('plots', 'code')->where(
                    fn (Builder $query): Builder => $query->where('site_id', $attributes['site_id'] ?? null),
                ),
            ],
            'capacity' => ['bail', 'required', 'integer', 'min:1', 'max:65535'],
        ], [
            'site_id.required' => 'Lokasi petak wajib dipilih.',
            'site_id.exists' => 'Lokasi yang dipilih tidak tersedia.',
            'code.required' => 'Kode petak wajib diisi.',
            'code.max' => 'Kode petak maksimal 40 karakter.',
            'code.unique' => 'Kode petak sudah digunakan pada lokasi ini.',
            'capacity.required' => 'Kapasitas petak wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa bilangan bulat.',
            'capacity.min' => 'Kapasitas petak minimal 1 pohon.',
            'capacity.max' => 'Kapasitas petak maksimal 65.535 pohon.',
        ])->validate();

        try {
            return DB::transaction(function () use ($data): Plot {
                $site = Site::query()
                    ->whereKey($data['site_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // The site row lock serializes creates through this action. The unique
                // database index remains the final guard for every writer.
                if ($site->plots()->where('code', $data['code'])->exists()) {
                    throw ValidationException::withMessages([
                        'code' => 'Kode petak sudah digunakan pada lokasi ini.',
                    ]);
                }

                return $site->plots()->create([
                    'code' => $data['code'],
                    'capacity' => (int) $data['capacity'],
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'code' => 'Kode petak sudah digunakan pada lokasi ini.',
            ]);
        }
    }
}
