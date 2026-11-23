<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WithdrawAdoptionRequestAction
{
    public function handle(
        User $actor,
        AdoptionRequest $request
    ): AdoptionRequest {
        return DB::transaction(function () use ($actor, $request): AdoptionRequest {
            $lockedRequest = AdoptionRequest::query()
                ->lockForUpdate()
                ->findOrFail($request->getKey());

            Gate::forUser($actor)->authorize('withdraw', $lockedRequest);

            if ($lockedRequest->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya permohonan yang belum diputuskan yang dapat ditarik.',
                ]);
            }

            $oldStatus = $lockedRequest->status;

            $lockedRequest->forceFill([
                'status' => 'withdrawn',
            ]);

            $lockedRequest->save();

            $lockedRequest->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'adoption_request.withdrawn',
                'from_status' => $oldStatus,
                'to_status' => 'withdrawn',
            ]);

            return $lockedRequest;
        });
    }
}
