<?php

namespace App\Actions\CareRecords;

use App\Models\CareRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VerifyCareRecord
{
    /**
     * Verifikasi hasil pelaksanaan perawatan fisik pohon oleh pengelola.
     *
     * Aturan:
     * 1. Verifikator harus berbeda dari pengirim laporan (AC 1).
     * 2. Status asal harus diajukan (AC 3).
     * 3. Status berubah menjadi terverifikasi (AC 3).
     */
    public function handle(User $actor, CareRecord $record, ?string $note = null): CareRecord
    {
        Gate::forUser($actor)->authorize('verify', $record);

        if ((int) $actor->getKey() === (int) $record->performed_by) {
            throw ValidationException::withMessages([
                'verifier' => 'Verifikator harus berbeda dari pengirim laporan.',
            ]);
        }

        if ($record->status !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => 'Hanya realisasi perawatan berstatus diajukan yang dapat diverifikasi.',
            ]);
        }

        return DB::transaction(function () use ($actor, $record, $note): CareRecord {
            $fromStatus = $record->status;

            $record->status = 'verified';
            $record->verified_by = $actor->getKey();
            $record->verified_at = now();
            if ($note !== null && trim($note) !== '') {
                $record->review_note = trim($note);
            }
            $record->save();

            $record->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_record.verified',
                'from_status' => $fromStatus,
                'to_status' => 'verified',
                'metadata' => array_filter([
                    'note' => $note,
                ]),
            ]);

            return $record;
        });
    }
}
