<?php

namespace App\Actions\PlantingBatches;

use App\Models\PlantingBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdatePlantingBatch
{
    /**
     * Koreksi data kelompok penanaman dan catat jejak audit.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, PlantingBatch $batch, array $attributes): PlantingBatch
    {
        Gate::forUser($actor)->authorize('update', $batch);

        $reason = isset($attributes['reason']) && is_string($attributes['reason'])
            ? trim($attributes['reason'])
            : '';

        if ($reason === '' || mb_strlen($reason) < 3) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan koreksi kelompok wajib diisi minimal 3 karakter.',
            ]);
        }

        $targetPlotId = isset($attributes['plot_id']) ? (int) $attributes['plot_id'] : (int) $batch->plot_id;

        $input = array_intersect_key($attributes, array_flip([
            'plot_id',
            'code',
            'planted_on',
            'notes',
        ]));

        if (isset($input['code']) && is_string($input['code'])) {
            $input['code'] = trim($input['code']);
        }

        if (array_key_exists('notes', $input) && is_string($input['notes'])) {
            $input['notes'] = trim($input['notes']);
            if ($input['notes'] === '') {
                $input['notes'] = null;
            }
        }

        $validated = Validator::make($input, [
            'plot_id' => ['sometimes', 'required', 'integer', 'exists:plots,id'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                Rule::unique('planting_batches', 'code')
                    ->where('plot_id', $targetPlotId)
                    ->ignore($batch->getKey()),
            ],
            'planted_on' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'code.required' => 'Kode kelompok wajib diisi.',
            'code.unique' => 'Kode kelompok sudah digunakan pada petak ini.',
            'code.max' => 'Kode kelompok maksimal 40 karakter.',
            'planted_on.required' => 'Tanggal tanam wajib diisi.',
            'planted_on.date' => 'Format tanggal tanam tidak valid.',
            'planted_on.before_or_equal' => 'Tanggal tanam tidak boleh di masa depan.',
            'notes.max' => 'Catatan maksimal 5000 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $batch, $validated, $reason): PlantingBatch {
            $lockedBatch = PlantingBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->getKey());

            // Proteksi perpindahan petak jika sudah memiliki pohon terhubung (AC2)
            if (isset($validated['plot_id']) && (int) $validated['plot_id'] !== (int) $lockedBatch->plot_id) {
                if ($lockedBatch->trees()->exists()) {
                    throw ValidationException::withMessages([
                        'plot_id' => 'Petak tidak dapat diubah karena kelompok sudah memiliki pohon terdaftar.',
                    ]);
                }
            }

            $oldAttributes = [
                'plot_id' => (int) $lockedBatch->plot_id,
                'code' => $lockedBatch->code,
                'planted_on' => $lockedBatch->planted_on?->toDateString(),
                'notes' => $lockedBatch->notes,
            ];

            $lockedBatch->fill($validated);
            $changes = $lockedBatch->getDirty();

            if (! empty($changes)) {
                $lockedBatch->save();

                $newAttributes = [
                    'plot_id' => (int) $lockedBatch->plot_id,
                    'code' => $lockedBatch->code,
                    'planted_on' => $lockedBatch->planted_on?->toDateString(),
                    'notes' => $lockedBatch->notes,
                ];

                $lockedBatch->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'planting_batch.updated',
                    'from_status' => null,
                    'to_status' => null,
                    'metadata' => [
                        'reason' => $reason,
                        'old' => $oldAttributes,
                        'new' => $newAttributes,
                    ],
                ]);
            }

            return $lockedBatch;
        });
    }
}
