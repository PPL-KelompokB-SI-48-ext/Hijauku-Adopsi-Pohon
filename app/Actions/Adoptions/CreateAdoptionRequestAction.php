<?php

namespace App\Actions\Adoptions;

use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAdoptionRequestAction
{
    public function handle(User $actor, array $data): AdoptionRequest
    {
        abort_unless($actor->isCitizen(), 403);

        return DB::transaction(function () use ($actor, $data) {
            $tree = Tree::query()
                ->whereKey($data['tree_id'])
                ->lockForUpdate()
                ->first();

            if (! $tree) {
                throw ValidationException::withMessages([
                    'tree_id' => 'Pohon tidak ditemukan.',
                ]);
            }

            if ($tree->adoptionAllocation()->exists()) {
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

            $reason = trim($data['reason'] ?? '');

            if ($reason === '') {
                throw ValidationException::withMessages([
                    'reason' => 'Alasan permohonan wajib diisi.',
                ]);
            }

            return AdoptionRequest::create([
                'tree_id' => $tree->id,
                'requester_id' => $actor->id,
                'reason' => $reason,
                'status' => 'submitted',
            ]);
        });
    }
}