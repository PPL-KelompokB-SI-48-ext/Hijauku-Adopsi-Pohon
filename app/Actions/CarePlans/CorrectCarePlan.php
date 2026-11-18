<?php

namespace App\Actions\CarePlans;

use App\Models\CarePlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CorrectCarePlan
{
    /**
     * Correct (edit) a care plan and record an audit log with explicit field changes.
     * Only plans in draft or published status can be corrected.
     * Cancelled or completed plans are rejected.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, CarePlan $plan, array $attributes): CarePlan
    {
        Gate::forUser($actor)->authorize('update', $plan);

        if (in_array($plan->status, ['cancelled', 'completed'], true)) {
            throw ValidationException::withMessages([
                'status' => match ($plan->status) {
                    'cancelled' => 'Rencana yang sudah dibatalkan tidak dapat dikoreksi.',
                    'completed' => 'Rencana yang sudah selesai tidak dapat dikoreksi.',
                    default => 'Koreksi tidak diizinkan pada status ini.',
                },
            ]);
        }

        $data = Validator::make($attributes, [
            'tree_id' => ['sometimes', 'integer', 'exists:trees,id'],
            'assignment_id' => ['nullable', 'integer', 'exists:field_assignments,id'],
            'planned_on' => ['sometimes', 'date'],
            'action' => ['sometimes', 'string', 'min:3', 'max:1000'],
            'priority' => ['sometimes', 'string', 'in:low,normal,medium,high,urgent'],
        ], [
            'tree_id.exists' => 'Pohon yang dipilih tidak ditemukan.',
            'assignment_id.exists' => 'Penugasan yang dipilih tidak ditemukan.',
            'planned_on.date' => 'Tanggal target tidak valid.',
            'action.min' => 'Aksi perawatan minimal 3 karakter.',
            'action.max' => 'Aksi perawatan maksimal 1000 karakter.',
            'priority.in' => 'Prioritas yang dipilih tidak valid.',
        ])->validate();

        return DB::transaction(function () use ($actor, $plan, $data): CarePlan {
            $trackableFields = ['tree_id', 'assignment_id', 'planned_on', 'action', 'priority'];
            $changes = [];

            foreach ($trackableFields as $field) {
                if (! array_key_exists($field, $data)) {
                    continue;
                }

                $oldValue = $plan->getAttribute($field);
                $newValue = $data[$field];

                // Normalize date comparison
                if ($field === 'planned_on' && $oldValue !== null) {
                    $oldValue = is_string($oldValue)
                        ? $oldValue
                        : $oldValue->toDateString();
                }

                if ((string) $oldValue !== (string) $newValue) {
                    $changes[$field] = [
                        'from' => $oldValue,
                        'to' => $newValue,
                    ];
                }
            }

            $plan->fill($data)->save();

            if (! empty($changes)) {
                $plan->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'care_plan.corrected',
                    'from_status' => $plan->status,
                    'to_status' => $plan->status,
                    'metadata' => [
                        'changes' => $changes,
                    ],
                ]);
            }

            return $plan;
        });
    }
}
