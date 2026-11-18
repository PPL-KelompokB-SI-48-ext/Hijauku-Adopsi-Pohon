<?php

namespace App\Actions\CareRecords;

use App\Models\CarePlan;
use App\Models\CareRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CorrectCareRecord
{
    /**
     * Koreksi data realisasi perawatan terverifikasi melalui keputusan tercatat.
     * Bukan edit diam-diam: mewajibkan alasan keputusan dan mencatat jejak perubahan.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, CareRecord $record, array $attributes): CareRecord
    {
        Gate::forUser($actor)->authorize('update', $record);

        if ((int) $actor->getKey() === (int) $record->performed_by) {
            throw ValidationException::withMessages([
                'actor' => 'Pengoreksi harus berbeda dari pengirim laporan.',
            ]);
        }

        if (isset($attributes['reason']) && is_string($attributes['reason'])) {
            $attributes['reason'] = trim($attributes['reason']);
        }

        if (isset($attributes['action']) && is_string($attributes['action'])) {
            $attributes['action'] = trim($attributes['action']);
        }

        // Normalize plan_id
        if (array_key_exists('plan_id', $attributes) && ($attributes['plan_id'] === '' || $attributes['plan_id'] === null)) {
            $attributes['plan_id'] = null;
        }

        $data = Validator::make($attributes, [
            'reason' => ['bail', 'required', 'string', 'min:5', 'max:1000'],
            'action' => ['sometimes', 'string', 'min:3', 'max:1000'],
            'performed_on' => ['sometimes', 'date_format:Y-m-d'],
            'plan_id' => ['nullable', 'integer', 'exists:care_plans,id'],
        ], [
            'reason.required' => 'Alasan keputusan koreksi wajib diisi.',
            'reason.min' => 'Alasan keputusan koreksi minimal 5 karakter.',
            'reason.max' => 'Alasan keputusan koreksi maksimal 1000 karakter.',
            'action.min' => 'Deskripsi tindakan minimal 3 karakter.',
            'action.max' => 'Deskripsi tindakan maksimal 1000 karakter.',
            'performed_on.date_format' => 'Format tanggal pelaksanaan tidak valid.',
            'plan_id.exists' => 'Rencana perawatan yang dipilih tidak valid.',
        ])->validate();

        // Validasi relasi pohon jika plan_id diubah
        if (! empty($data['plan_id'])) {
            $plan = CarePlan::query()->find($data['plan_id']);

            if ($plan && (int) $plan->tree_id !== (int) $record->tree_id) {
                throw ValidationException::withMessages([
                    'plan_id' => 'Rencana perawatan tidak sesuai dengan pohon realisasi ini.',
                ]);
            }
        }

        return DB::transaction(function () use ($actor, $record, $data): CareRecord {
            $trackableFields = ['action', 'performed_on', 'plan_id'];
            $changes = [];

            foreach ($trackableFields as $field) {
                if (! array_key_exists($field, $data)) {
                    continue;
                }

                $oldValue = $record->getAttribute($field);
                $newValue = $data[$field];

                if ($field === 'performed_on' && $oldValue !== null) {
                    $oldValue = is_string($oldValue) ? $oldValue : $oldValue->toDateString();
                }

                if ((string) $oldValue !== (string) $newValue) {
                    $changes[$field] = [
                        'from' => $oldValue,
                        'to' => $newValue,
                    ];
                }
            }

            if (isset($data['action'])) {
                $record->action = $data['action'];
            }
            if (isset($data['performed_on'])) {
                $record->performed_on = $data['performed_on'];
            }
            if (array_key_exists('plan_id', $data)) {
                $record->plan_id = $data['plan_id'];
            }

            $record->save();

            $record->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_record.corrected',
                'from_status' => $record->status,
                'to_status' => $record->status,
                'metadata' => [
                    'decision_reason' => $data['reason'],
                    'changes' => $changes,
                ],
            ]);

            return $record;
        });
    }
}
