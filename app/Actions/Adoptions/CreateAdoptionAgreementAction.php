<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateAdoptionAgreementAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): AdoptionAgreement
    {
        Gate::forUser($actor)->authorize('create', AdoptionAgreement::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'request_id', 'starts_on', 'ends_on', 'terms',
        ]));

        if (isset($attributes['terms']) && is_string($attributes['terms'])) {
            $attributes['terms'] = trim($attributes['terms']);
        }

        $data = Validator::make($attributes, [
            'request_id' => ['required', 'integer', 'exists:adoption_requests,id'],
            'starts_on' => ['bail', 'required', 'date_format:Y-m-d'],
            'ends_on' => ['bail', 'required', 'date_format:Y-m-d', 'after:starts_on'],
            'terms' => ['required', 'string'],
        ], [
            'request_id.required' => 'Permohonan adopsi wajib dipilih.',
            'request_id.exists' => 'Permohonan adopsi tidak ditemukan.',
            'starts_on.required' => 'Tanggal mulai adopsi wajib diisi.',
            'ends_on.required' => 'Tanggal akhir adopsi wajib diisi.',
            'starts_on.date_format' => 'Tanggal mulai tidak valid.',
            'ends_on.date_format' => 'Tanggal akhir tidak valid.',
            'ends_on.after' => 'Tanggal akhir harus setelah tanggal mulai.',
            'terms.required' => 'Syarat komitmen wajib diisi.',
        ])->validate();

        return DB::transaction(function () use ($actor, $data): AdoptionAgreement {
            $request = AdoptionRequest::query()
                ->lockForUpdate()
                ->findOrFail($data['request_id']);

            if ($request->status !== 'approved') {
                throw ValidationException::withMessages([
                    'request_id' => 'Kesepakatan hanya dapat dibuat dari permohonan yang disetujui.',
                ]);
            }

            if ($request->agreement()->exists()) {
                throw ValidationException::withMessages([
                    'request_id' => 'Permohonan ini sudah memiliki kesepakatan adopsi.',
                ]);
            }

            $tree = Tree::query()
                ->whereKey($request->tree_id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasBlockingAllocation = $tree->adoptionAllocations()
                ->whereIn('status', TreeAdoptionAllocation::VALID_STATUSES)
                ->exists();

            if ($hasBlockingAllocation) {
                throw ValidationException::withMessages([
                    'request_id' => 'Pohon sudah memiliki reservasi atau alokasi adopsi.',
                ]);
            }

            $now = now();

            $agreement = AdoptionAgreement::query()->create([
                'request_id' => $request->getKey(),
                'tree_id' => $tree->getKey(),
                'adopter_id' => $request->requester_id,
                'terms' => $data['terms'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'status' => 'awaiting_acceptance',
                'accepted_at' => null,
            ]);

            $allocation = TreeAdoptionAllocation::query()->create([
                'tree_id' => $tree->getKey(),
                'request_id' => $request->getKey(),
                'agreement_id' => $agreement->getKey(),
                'status' => 'awaiting_acceptance',
                'reserved_at' => $now,
            ]);

            $agreement->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'adoption_agreement.created',
                'from_status' => null,
                'to_status' => 'awaiting_acceptance',
                'metadata' => [
                    'request_id' => $request->getKey(),
                    'tree_id' => $tree->getKey(),
                    'adopter_id' => $request->requester_id,
                    'allocation_id' => $allocation->getKey(),
                    'starts_on' => $data['starts_on'],
                    'ends_on' => $data['ends_on'],
                ],
            ]);

            return $agreement;
        });
    }
}
