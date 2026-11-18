<?php

namespace App\Actions\CarePlans;

use App\Models\CarePlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateCarePlanPriority
{
    /**
     * Update the care plan priority and record an audit log with the explicit reason.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, CarePlan $plan, array $attributes): CarePlan
    {
        Gate::forUser($actor)->authorize('update', $plan);

        if ($plan->status === 'cancelled') {
            throw ValidationException::withMessages([
                'priority' => 'Tidak dapat mengubah prioritas rencana perawatan yang telah dibatalkan.',
            ]);
        }

        if (isset($attributes['reason']) && is_string($attributes['reason'])) {
            $attributes['reason'] = trim($attributes['reason']);
        }

        $data = Validator::make($attributes, [
            'priority' => ['bail', 'required', 'string', 'in:low,normal,medium,high,urgent'],
            'reason' => ['bail', 'required', 'string', 'min:3', 'max:500'],
        ], [
            'priority.required' => 'Prioritas wajib dipilih.',
            'priority.in' => 'Prioritas yang dipilih tidak valid.',
            'reason.required' => 'Alasan perubahan prioritas wajib diisi.',
            'reason.min' => 'Alasan perubahan prioritas minimal 3 karakter.',
            'reason.max' => 'Alasan perubahan prioritas maksimal 500 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $plan, $data): CarePlan {
            $oldPriority = $plan->priority ?? 'normal';
            $newPriority = $data['priority'];

            $plan->priority = $newPriority;
            $plan->save();

            $plan->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_plan.priority_updated',
                'from_status' => null,
                'to_status' => null,
                'metadata' => [
                    'old_priority' => $oldPriority,
                    'new_priority' => $newPriority,
                    'reason' => $data['reason'],
                ],
            ]);

            return $plan;
        });
    }
}

