<?php

namespace App\Actions\Trees;

use App\Models\Plot;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateTree
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Tree
    {
        Gate::forUser($actor)->authorize('create', Tree::class);

        $plotId = (int) ($attributes['plot_id'] ?? 0);

        $data = Validator::make($attributes, [
            'species_id' => ['required', 'integer', Rule::exists('tree_species', 'id')->where('is_active', true)],
            'plot_id' => ['required', 'integer', 'exists:plots,id'],
            'batch_id' => [
                'nullable',
                'integer',
                Rule::exists('planting_batches', 'id')->where('plot_id', $plotId),
            ],
            'code' => ['required', 'string', 'max:60', 'unique:trees,code'],
            'planted_on' => ['nullable', 'date'],
            'initial_condition' => ['required', 'string', 'max:32'],
        ], [
            'species_id.exists' => 'Jenis pohon yang dipilih tidak aktif atau tidak valid.',
            'plot_id.required' => 'Petak tanam wajib dipilih.',
            'plot_id.exists' => 'Petak tanam yang dipilih tidak valid.',
            'batch_id.exists' => 'Kelompok penanaman yang dipilih harus berada pada petak yang sama.',
            'code.unique' => 'Kode pohon sudah terdaftar.',
        ])->validate();

        return DB::transaction(function () use ($data): Tree {
            $plot = Plot::query()
                ->lockForUpdate()
                ->findOrFail($data['plot_id']);

            if ($plot->status !== 'open') {
                throw ValidationException::withMessages([
                    'plot_id' => 'Petak ini tidak terbuka untuk pencatatan pohon baru.',
                ]);
            }

            $currentTreeCount = $plot->trees()->count();

            if ($currentTreeCount >= (int) $plot->capacity) {
                throw ValidationException::withMessages([
                    'plot_id' => 'Kapasitas petak sudah penuh. Pilih petak lain yang masih tersedia.',
                ]);
            }

            return Tree::query()->create($data);
        }, attempts: 3);
    }
}
