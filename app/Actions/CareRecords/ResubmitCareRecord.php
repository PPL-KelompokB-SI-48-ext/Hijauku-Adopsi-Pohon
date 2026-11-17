<?php

namespace App\Actions\CareRecords;

use App\Models\CareRecord;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ResubmitCareRecord
{
    /**
     * Ajukan ulang realisasi perawatan oleh petugas setelah melakukan perbaikan sesuai catatan pengelola.
     * Mengubah status dari 'returned' menjadi 'submitted'.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, CareRecord $record, array $attributes): CareRecord
    {
        Gate::forUser($actor)->authorize('resubmit', $record);

        if ((int) $actor->getKey() !== (int) $record->performed_by) {
            throw ValidationException::withMessages([
                'actor' => 'Hanya petugas pengirim laporan yang dapat memperbaiki dan mengajukan ulang realisasi ini.',
            ]);
        }

        if ($record->status !== 'returned') {
            throw ValidationException::withMessages([
                'status' => 'Hanya realisasi perawatan yang berstatus dikembalikan yang dapat diajukan ulang.',
            ]);
        }

        if (isset($attributes['action']) && is_string($attributes['action'])) {
            $attributes['action'] = trim($attributes['action']);
        }

        $data = Validator::make($attributes, [
            'action' => ['required', 'string', 'min:3', 'max:1000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'performed_on' => ['sometimes', 'date_format:Y-m-d'],
        ], [
            'action.required' => 'Deskripsi tindakan perbaikan wajib diisi.',
            'action.min' => 'Deskripsi tindakan minimal 3 karakter.',
            'action.max' => 'Deskripsi tindakan maksimal 1000 karakter.',
            'photo.mimes' => 'Bukti foto harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ])->validate();

        return DB::transaction(function () use ($actor, $record, $data, $attributes): CareRecord {
            $photoPath = $record->photo_path;

            if (isset($attributes['photo']) && $attributes['photo'] instanceof UploadedFile) {
                $photoPath = $attributes['photo']->store('care-records/photos', 'private');
            }

            $fromStatus = $record->status;

            $record->status = 'submitted';
            $record->action = $data['action'];
            $record->photo_path = $photoPath;
            if (isset($data['performed_on'])) {
                $record->performed_on = $data['performed_on'];
            }
            $record->save();

            $record->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_record.resubmitted',
                'from_status' => $fromStatus,
                'to_status' => 'submitted',
                'metadata' => [
                    'previous_review_note' => $record->review_note,
                    'has_new_photo' => isset($attributes['photo']) && $attributes['photo'] instanceof UploadedFile,
                ],
            ]);

            return $record;
        });
    }
}
