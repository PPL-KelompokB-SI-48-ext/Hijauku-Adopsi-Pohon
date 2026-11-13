<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateAdoptionRequestAction
{
    public function handle(User $actor, array $data): AdoptionRequest
    {
        Gate::forUser($actor)->authorize('create', AdoptionRequest::class);

        $reasonInput = $data['reason'] ?? null;
        $reason = is_string($reasonInput) ? trim($reasonInput) : '';

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan permohonan wajib diisi.',
            ]);
        }

        if (mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan permohonan maksimal 1000 karakter.',
            ]);
        }

        return DB::transaction(function () use ($actor, $data, $reason) {
            $tree = Tree::query()
                ->whereKey($data['tree_id'] ?? null)
                ->lockForUpdate()
                ->first();

            if (! $tree) {
                throw ValidationException::withMessages([
                    'tree_id' => 'Pohon tidak ditemukan.',
                ]);
            }

            $hasValidAllocation = $tree->adoptionAllocation()
                ->whereIn(
                    'status',
                    TreeAdoptionAllocation::VALID_STATUSES
                )
                ->exists();

            if ($hasValidAllocation) {
                throw ValidationException::withMessages([
                    'tree_id' => 'Pohon sudah tidak tersedia untuk diajukan.',
                ]);
            }

            $activeRequestExists = AdoptionRequest::query()
                ->where('tree_id', $tree->id)
                ->where('requester_id', $actor->id)
                ->whereIn('status', ['draft', 'submitted'])
                ->exists();

            if ($activeRequestExists) {
                throw ValidationException::withMessages([
                    'tree_id' => 'Anda sudah memiliki permohonan aktif untuk pohon ini.',
                ]);
            }

            $request = AdoptionRequest::query()->create([
                'tree_id' => $tree->id,
                'requester_id' => $actor->id,
                'reason' => $reason,
                'status' => 'submitted',
            ]);

            $request->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'adoption_request.created',
                'from_status' => null,
                'to_status' => 'submitted',
                'metadata' => [
                    'reason' => $reason,
                ],
            ]);

            return $request;
        });
    }
}
