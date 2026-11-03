<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DecideAdoptionRequestAction
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function handle(
        User $actor,
        AdoptionRequest $request,
        array $attributes
    ): AdoptionRequest {
        $attributes = array_intersect_key($attributes, array_flip([
            'decision',
            'decision_reason',
        ]));

        if (isset($attributes['decision_reason']) && is_string($attributes['decision_reason'])) {
            $attributes['decision_reason'] = trim($attributes['decision_reason']);
        }

        $data = Validator::make($attributes, [
            'decision' => ['required', 'in:approved,rejected'],
            'decision_reason' => [
                'nullable',
                'required_if:decision,rejected',
                'string',
                'max:1000',
            ],
        ], [
            'decision.required' => 'Keputusan wajib dipilih.',
            'decision.in' => 'Keputusan tidak valid.',
            'decision_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'decision_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $request, $data): AdoptionRequest {
            $lockedRequest = AdoptionRequest::query()
                ->lockForUpdate()
                ->findOrFail($request->getKey());

            Gate::forUser($actor)->authorize('review', $lockedRequest);

            if ($lockedRequest->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'decision' => 'Hanya permohonan yang sudah diajukan yang dapat diputuskan.',
                ]);
            }

            $oldStatus = $lockedRequest->status;
            $newStatus = $data['decision'];

            $lockedRequest->forceFill([
                'status' => $newStatus,
                'reviewer_id' => $actor->getKey(),
                'decision_reason' => $newStatus === 'rejected'
                    ? $data['decision_reason']
                    : null,
            ]);

            $lockedRequest->save();

            $lockedRequest->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'adoption_request.decided',
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'metadata' => [
                    'decision_reason' => $lockedRequest->decision_reason,
                ],
            ]);

            return $lockedRequest;
        });
    }
}