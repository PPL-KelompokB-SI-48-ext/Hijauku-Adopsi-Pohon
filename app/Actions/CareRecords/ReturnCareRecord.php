<?php

namespace App\Actions\CareRecords;

use App\Models\CareRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReturnCareRecord
{
    /**
     * Kembalikan / tolak realisasi perawatan dengan catatan alasan yang wajib diisi.
     *
     * Aturan:
     * 1. Verifikator harus berbeda dari pengirim laporan (AC 1).
     * 2. Penolakan wajib beralasan dan bisa dibaca petugas (AC 2).
     * 3. Status asal harus diajukan, status baru dikembalikan (AC 3).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, CareRecord $record, array $attributes): CareRecord
    {
        Gate::forUser($actor)->authorize('verify', $record);

        if ((int) $actor->getKey() === (int) $record->performed_by) {
            throw ValidationException::withMessages([
                'verifier' => 'Verifikator harus berbeda dari pengirim laporan.',
            ]);
        }

        if (! in_array($record->status, ['submitted', 'verified'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Hanya realisasi perawatan berstatus diajukan atau terverifikasi yang dapat dikembalikan.',
            ]);
        }

        if (isset($attributes['review_note']) && is_string($attributes['review_note'])) {
            $attributes['review_note'] = trim($attributes['review_note']);
        }

        $data = Validator::make($attributes, [
            'review_note' => ['bail', 'required', 'string', 'min:5', 'max:1000'],
        ], [
            'review_note.required' => 'Alasan penolakan wajib diisi.',
            'review_note.string' => 'Alasan penolakan harus berupa teks.',
            'review_note.min' => 'Alasan penolakan minimal 5 karakter.',
            'review_note.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ])->validate();

        return DB::transaction(function () use ($actor, $record, $data): CareRecord {
            $fromStatus = $record->status;

            $record->status = 'returned';
            $record->verified_by = $actor->getKey();
            $record->verified_at = now();
            $record->review_note = $data['review_note'];
            $record->save();

            $record->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_record.returned',
                'from_status' => $fromStatus,
                'to_status' => 'returned',
                'metadata' => [
                    'reason' => $data['review_note'],
                ],
            ]);

            return $record;
        });
    }
}
