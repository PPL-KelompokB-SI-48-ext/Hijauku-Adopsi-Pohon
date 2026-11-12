<?php

namespace App\Actions\PlantingBatches;

use App\Models\PlantingBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreatePlantingBatch
{
    /**
     * Catat pelaksanaan penanaman dalam kelompok penanaman baru dan simpan jejak auditnya.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): PlantingBatch
    {
        Gate::forUser($actor)->authorize('create', PlantingBatch::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'plot_id',
            'code',
            'planted_on',
            'notes',
        ]));

        if (isset($attributes['code']) && is_string($attributes['code'])) {
            $attributes['code'] = trim($attributes['code']);
        }

        if (isset($attributes['notes']) && is_string($attributes['notes'])) {
            $attributes['notes'] = trim($attributes['notes']);
            if ($attributes['notes'] === '') {
                $attributes['notes'] = null;
            }
        }

        $plotId = $attributes['plot_id'] ?? null;

        $validated = Validator::make($attributes, [
            'plot_id' => ['required', 'integer', 'exists:plots,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('planting_batches', 'code')->where(function ($query) use ($plotId) {
                    return $query->where('plot_id', $plotId);
                }),
            ],
            'planted_on' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
        ], [
            'plot_id.required' => 'Petak tanam wajib dipilih.',
            'plot_id.exists' => 'Petak tanam yang dipilih tidak valid.',
            'code.required' => 'Kode kelompok penanaman wajib diisi.',
            'code.max' => 'Kode kelompok penanaman maksimal 40 karakter.',
            'code.unique' => 'Kode kelompok penanaman sudah digunakan pada petak ini.',
            'planted_on.required' => 'Tanggal pelaksanaan penanaman wajib diisi.',
            'planted_on.before_or_equal' => 'Tanggal pelaksanaan penanaman tidak boleh di masa depan.',
        ])->validate();

        return DB::transaction(function () use ($actor, $validated): PlantingBatch {
            $batch = new PlantingBatch($validated);
            $batch->save();

            $batch->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'planting_batch.created',
                'from_status' => null,
                'to_status' => 'recorded',
                'metadata' => [
                    'code' => $batch->code,
                    'plot_id' => $batch->plot_id,
                    'planted_on' => $batch->planted_on?->toDateString(),
                ],
            ]);

            return $batch;
        });
    }
}
