<?php

namespace App\Actions\GrowthObservations;

use App\Models\GrowthObservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VerifyGrowthObservation
{
    /**
     * Verifikasi hasil pengamatan pertumbuhan pohon oleh pengelola.
     *
     * Aturan:
     * 1. Verifikator harus berbeda dari petugas pengamat pengirim laporan.
     * 2. Status asal harus diajukan (submitted), berubah menjadi terverifikasi (verified).
     * 3. Hanya pengamatan terverifikasi yang masuk ringkasan perkembangan (AC 1).
     */
    public function handle(User $actor, GrowthObservation $observation, ?string $note = null): GrowthObservation
    {
        Gate::forUser($actor)->authorize('verify', $observation);

        if ((int) $actor->getKey() === (int) $observation->observed_by) {
            throw ValidationException::withMessages([
                'verifier' => 'Verifikator harus berbeda dari pengamat pengirim laporan.',
            ]);
        }

        return DB::transaction(function () use ($actor, $observation, $note): GrowthObservation {
            $locked = GrowthObservation::query()
                ->whereKey($observation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pengamatan pertumbuhan berstatus diajukan yang dapat diverifikasi.',
                ]);
            }

            $fromStatus = $locked->status;
            $trimmedNote = $note !== null ? trim($note) : null;

            $locked->status = 'verified';
            $locked->verified_by = $actor->getKey();
            $locked->verified_at = now();
            if ($trimmedNote !== null && $trimmedNote !== '') {
                $locked->review_note = $trimmedNote;
            }
            $locked->save();

            $locked->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'growth_observation.verified',
                'from_status' => $fromStatus,
                'to_status' => 'verified',
                'metadata' => array_filter([
                    'note' => $trimmedNote,
                ], fn ($value): bool => $value !== null && $value !== ''),
            ]);

            $observation->setRawAttributes($locked->getAttributes(), true);

            return $locked;
        });
    }
}
