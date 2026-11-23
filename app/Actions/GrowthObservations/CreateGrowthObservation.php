<?php

namespace App\Actions\GrowthObservations;

use App\Models\GrowthObservation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateGrowthObservation
{
    /**
     * Catat pengamatan pertumbuhan pohon terukur beserta bukti foto opsional.
     * Hanya petugas lapangan yang diizinkan; status awal selalu ditetapkan server sebagai submitted.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): GrowthObservation
    {
        Gate::forUser($actor)->authorize('create', GrowthObservation::class);

        $photoInput = $attributes['photo'] ?? null;
        $hasUploadedFile = $photoInput instanceof UploadedFile;
        $hasStoredPhotoPath = is_string($photoInput) && trim($photoInput) !== '';

        $validationAttributes = $attributes;
        if (! $hasUploadedFile) {
            unset($validationAttributes['photo']);
        }

        $data = Validator::make($validationAttributes, [
            'tree_id' => ['bail', 'required', 'integer', 'exists:trees,id'],
            'observed_on' => ['bail', 'required', 'date_format:Y-m-d'],
            'height_cm' => ['bail', 'required', 'numeric', 'gt:0', 'max:999999.99'],
            'diameter_cm' => ['bail', 'required', 'numeric', 'gt:0', 'max:999999.99'],
            'condition' => ['bail', 'required', 'string', Rule::in(array_keys(GrowthObservation::conditionOptions()))],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'tree_id.required' => 'Pohon wajib dipilih.',
            'tree_id.exists' => 'Pohon yang dipilih tidak valid.',
            'observed_on.required' => 'Tanggal pengamatan wajib diisi.',
            'observed_on.date_format' => 'Tanggal pengamatan harus berupa tanggal yang valid (Y-m-d).',
            'height_cm.required' => 'Tinggi pohon wajib diisi.',
            'height_cm.numeric' => 'Tinggi pohon harus berupa angka.',
            'height_cm.gt' => 'Tinggi pohon harus berupa bilangan positif lebih dari 0.',
            'height_cm.max' => 'Tinggi pohon melebihi batas maksimum.',
            'diameter_cm.required' => 'Diameter batang wajib diisi.',
            'diameter_cm.numeric' => 'Diameter batang harus berupa angka.',
            'diameter_cm.gt' => 'Diameter batang harus berupa bilangan positif lebih dari 0.',
            'diameter_cm.max' => 'Diameter batang melebihi batas maksimum.',
            'condition.required' => 'Kondisi pohon wajib dipilih.',
            'condition.in' => 'Kondisi pohon yang dipilih tidak valid.',
            'photo.file' => 'Bukti foto tidak valid.',
            'photo.mimes' => 'Bukti foto harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ])->validate();

        $validatedStoredPath = null;
        if (! $hasUploadedFile && $hasStoredPhotoPath) {
            $validatedStoredPath = $this->validateStoredPhotoPath((string) $photoInput);
        } elseif (! $hasUploadedFile && $photoInput !== null && $photoInput !== '') {
            throw ValidationException::withMessages([
                'photo' => 'Bukti foto tidak valid.',
            ]);
        }

        try {
            return DB::transaction(function () use ($actor, $data, $photoInput, $hasUploadedFile, $validatedStoredPath): GrowthObservation {
                $alreadyExists = GrowthObservation::query()
                    ->where('tree_id', $data['tree_id'])
                    ->whereDate('observed_on', $data['observed_on'])
                    ->exists();

                if ($alreadyExists) {
                    throw ValidationException::withMessages([
                        'observed_on' => 'Pengamatan untuk pohon ini pada tanggal tersebut sudah tercatat.',
                    ]);
                }

                $photoPath = $validatedStoredPath;
                if ($hasUploadedFile && $photoInput instanceof UploadedFile) {
                    $photoPath = $photoInput->store('growth-observations/photos', 'private');
                }

                $observation = new GrowthObservation;
                $observation->tree_id = (int) $data['tree_id'];
                $observation->observed_by = $actor->getKey();
                $observation->observed_on = $data['observed_on'];
                $observation->height_cm = round((float) $data['height_cm'], 2);
                $observation->diameter_cm = round((float) $data['diameter_cm'], 2);
                $observation->condition = $data['condition'];
                $observation->photo_path = $photoPath;
                $observation->status = 'submitted';
                $observation->save();

                $observation->activityLogs()->create([
                    'actor_id' => $actor->getKey(),
                    'action' => 'growth_observation.created',
                    'from_status' => null,
                    'to_status' => 'submitted',
                    'metadata' => [
                        'tree_id' => $observation->tree_id,
                        'observed_on' => $data['observed_on'],
                        'height_cm' => (float) $observation->height_cm,
                        'diameter_cm' => (float) $observation->diameter_cm,
                        'condition' => $observation->condition,
                        'has_photo' => $photoPath !== null,
                    ],
                ]);

                return $observation;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'observed_on' => 'Pengamatan untuk pohon ini pada tanggal tersebut sudah tercatat.',
            ]);
        }
    }

    /**
     * Memastikan path file foto yang berasal dari upload Filament valid, berada di disk privat, dan <= 5 MB.
     */
    private function validateStoredPhotoPath(string $path): string
    {
        $normalized = ltrim(trim($path), '/');

        if (! preg_match('/^growth-observations\/photos\/[A-Za-z0-9._-]+\.(jpg|jpeg|png|webp)$/i', $normalized)) {
            throw ValidationException::withMessages([
                'photo' => 'Bukti foto harus berformat JPG, PNG, atau WebP.',
            ]);
        }

        $disk = Storage::disk('private');

        if (! $disk->exists($normalized)) {
            throw ValidationException::withMessages([
                'photo' => 'Berkas bukti foto tidak ditemukan di penyimpanan privat.',
            ]);
        }

        if ($disk->size($normalized) > 5120 * 1024) {
            throw ValidationException::withMessages([
                'photo' => 'Ukuran foto maksimal 5 MB.',
            ]);
        }

        return $normalized;
    }
}
