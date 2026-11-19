<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionAgreement;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AcceptAdoptionAgreementAction
{
    public function handle(User $actor, AdoptionAgreement $agreement): AdoptionAgreement
    {
        return DB::transaction(function () use ($actor, $agreement) {
            $agreement = AdoptionAgreement::query()
                ->whereKey($agreement->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::forUser($actor)->authorize('accept', $agreement);

            if ($agreement->status !== 'awaiting_acceptance') {
                throw ValidationException::withMessages([
                    'agreement' => 'Kesepakatan ini tidak dapat diterima.',
                ]);
            }

            $tree = Tree::query()
                ->whereKey($agreement->tree_id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasBlockingAllocation = TreeAdoptionAllocation::query()
                ->where('tree_id', $tree->getKey())
                ->whereIn('status', TreeAdoptionAllocation::VALID_STATUSES)
                ->exists();

            if ($hasBlockingAllocation) {
                throw ValidationException::withMessages([
                    'agreement' => 'Pohon sudah dialokasikan kepada pihak lain.',
                ]);
            }

            $now = now();

            $allocation = TreeAdoptionAllocation::create([
                'tree_id' => $tree->getKey(),
                'request_id' => $agreement->request_id,
                'agreement_id' => $agreement->getKey(),
                'status' => 'active',
                'reserved_at' => $now,
            ]);

            $agreement->update([
                'status' => 'active',
                'accepted_at' => $now,
            ]);

            $agreement->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'adoption_agreement.accepted',
                'from_status' => 'awaiting_acceptance',
                'to_status' => 'active',
                'metadata' => [
                    'allocation_id' => $allocation->getKey(),
                    'tree_id' => $tree->getKey(),
                ],
            ]);

            return $agreement->refresh();
        });
    }
}
