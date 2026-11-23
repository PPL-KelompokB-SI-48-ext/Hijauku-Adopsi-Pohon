<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionAgreement;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TerminateAdoptionAgreementAction
{
    public function handle(
        User $actor,
        AdoptionAgreement $agreement,
        array $attributes
    ): AdoptionAgreement {
        $reason = is_string($attributes['reason'] ?? null)
            ? trim($attributes['reason'])
            : '';

        $data = Validator::make(['reason' => $reason], [
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'reason.required' => 'Alasan penghentian wajib diisi.',
            'reason.min' => 'Alasan penghentian minimal 3 karakter.',
            'reason.max' => 'Alasan penghentian maksimal 1000 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $agreement, $data): AdoptionAgreement {
            $lockedAgreement = AdoptionAgreement::query()
                ->whereKey($agreement->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::forUser($actor)->authorize('terminate', $lockedAgreement);

            if ($lockedAgreement->status !== 'active') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya kesepakatan aktif yang dapat dihentikan.',
                ]);
            }

            if ($lockedAgreement->ends_on && today()->gt($lockedAgreement->ends_on)) {
                throw ValidationException::withMessages([
                    'status' => 'Masa kesepakatan telah berakhir dan akan diselesaikan secara otomatis.',
                ]);
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
                throw ValidationException::withMessages([
                    'status' => 'Alokasi aktif untuk kesepakatan ini tidak ditemukan.',
                ]);
            }

            $lockedAgreement->forceFill([
                'status' => 'terminated',
            ])->save();

            $allocation->forceFill([
                'status' => TreeAdoptionAllocation::RELEASED_STATUS,
            ])->save();

            $lockedAgreement->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'adoption_agreement.terminated',
                'from_status' => 'active',
                'to_status' => 'terminated',
                'metadata' => [
                    'reason' => $data['reason'],
                    'allocation_id' => $allocation->getKey(),
                    'tree_id' => $lockedAgreement->tree_id,
                    'terminated_at' => now()->toDateTimeString(),
                ],
            ]);

            return $lockedAgreement->refresh();
        });
    }
}
