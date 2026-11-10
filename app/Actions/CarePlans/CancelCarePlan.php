<?php

namespace App\Actions\CarePlans;

use App\Models\CarePlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CancelCarePlan
{
    /**
     * Cancel a care plan and record an audit log with the mandatory reason.
     * Only plans in draft or published status can be cancelled.
     * Completed or already-cancelled plans are rejected.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, CarePlan $plan, array $attributes): CarePlan
    {
        Gate::forUser($actor)->authorize('update', $plan);

        if (in_array($plan->status, ['completed', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'status' => match ($plan->status) {
                    'cancelled' => 'Rencana perawatan ini sudah dibatalkan sebelumnya.',
                    'completed' => 'Rencana perawatan yang sudah selesai tidak dapat dibatalkan.',
                    default => 'Transisi status tidak diizinkan.',
                },
            ]);
        }

        if (isset($attributes['reason']) && is_string($attributes['reason'])) {
            $attributes['reason'] = trim($attributes['reason']);
        }

        $data = Validator::make($attributes, [
            'reason' => ['bail', 'required', 'string', 'min:3', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 3 karakter.',
            'reason.max' => 'Alasan pembatalan maksimal 500 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $plan, $data): CarePlan {
            $oldStatus = $plan->status;

            $plan->status = 'cancelled';
            $plan->save();

            $plan->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_plan.cancelled',
                'from_status' => $oldStatus,
                'to_status' => 'cancelled',
                'metadata' => [
                    'reason' => $data['reason'],
                ],
            ]);

            return $plan;
        });
    }
}
