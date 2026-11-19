<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionAgreement;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use Illuminate\Support\Facades\DB;

class CompleteExpiredAdoptionAgreementAction
{
    public function handle(AdoptionAgreement $agreement): ?AdoptionAgreement
    {
        return DB::transaction(function () use ($agreement): ?AdoptionAgreement {
            $lockedAgreement = AdoptionAgreement::query()
                ->whereKey($agreement->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedAgreement
                || $lockedAgreement->status !== 'active'
                || ! $lockedAgreement->ends_on
                || ! today()->gt($lockedAgreement->ends_on)) {
                return null;
            }

            Tree::query()
                ->whereKey($lockedAgreement->tree_id)
                ->lockForUpdate()
                ->firstOrFail();

            $allocation = TreeAdoptionAllocation::query()
                ->where('agreement_id', $lockedAgreement->getKey())
                ->lockForUpdate()
                ->first();

            if (! $allocation || $allocation->status !== 'active') {
                return null;
            }

            $lockedAgreement->forceFill([
                'status' => 'completed',
            ])->save();

            $allocation->forceFill([
                'status' => TreeAdoptionAllocation::RELEASED_STATUS,
            ])->save();

            $lockedAgreement->activityLogs()->create([
                'actor_id' => null,
                'action' => 'adoption_agreement.completed',
                'from_status' => 'active',
                'to_status' => 'completed',
                'metadata' => [
                    'reason' => 'Masa kesepakatan telah berakhir.',
                    'allocation_id' => $allocation->getKey(),
                    'tree_id' => $lockedAgreement->tree_id,
                    'completed_at' => now()->toDateTimeString(),
                    'completed_automatically' => true,
                ],
            ]);

            return $lockedAgreement->refresh();
        });
    }
}
