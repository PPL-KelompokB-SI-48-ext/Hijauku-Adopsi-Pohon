<?php

namespace App\Actions\GrowthObservations;

use App\Models\GrowthObservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReturnGrowthObservation
{
    /**
     * Kembalikan pengamatan pertumbuhan yang bermasalah dengan alasan wajib diisi.
     *
     * Aturan:
     * 1. Verifikator harus berbeda dari petugas pengamat pengirim laporan.
     * 2. Alasan penolakan / pengembalian wajib diisi agar bisa dibaca petugas.
     * 3. Status asal harus diajukan (submitted), berubah menjadi dikembalikan (returned).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, GrowthObservation $observation, array $attributes): GrowthObservation
    {
        Gate::forUser($actor)->authorize('verify', $observation);

        if ((int) $actor->getKey() === (int) $observation->observed_by) {
            throw ValidationException::withMessages([
                'verifier' => 'Verifikator harus berbeda dari pengamat pengirim laporan.',
            ]);
        }

        if (isset($attributes['review_note']) && is_string($attributes['review_note'])) {
            $attributes['review_note'] = trim($attributes['review_note']);
        }

        $data = Validator::make($attributes, [
            'review_note' => ['bail', 'required', 'string', 'min:5', 'max:1000'],
        ], [
            'review_note.required' => 'Alasan pengembalian wajib diisi.',
            'review_note.string' => 'Alasan pengembalian harus berupa teks.',
            'review_note.min' => 'Alasan pengembalian minimal 5 karakter.',
            'review_note.max' => 'Alasan pengembalian maksimal 1000 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $observation, $data): GrowthObservation {
            $locked = GrowthObservation::query()
                ->whereKey($observation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pengamatan pertumbuhan berstatus diajukan yang dapat dikembalikan.',
                ]);
            }

            $fromStatus = $locked->status;

            $locked->status = 'returned';
            $locked->verified_by = $actor->getKey();
            $locked->verified_at = now();
            $locked->review_note = $data['review_note'];
            $locked->save();

            $locked->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'growth_observation.returned',
                'from_status' => $fromStatus,
                'to_status' => 'returned',
                'metadata' => [
                    'reason' => $data['review_note'],
                ],
            ]);

            $observation->setRawAttributes($locked->getAttributes(), true);

            return $locked;
        });
    }
}
