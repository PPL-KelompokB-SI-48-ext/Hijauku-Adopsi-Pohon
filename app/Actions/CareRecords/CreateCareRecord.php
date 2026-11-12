<?php

namespace App\Actions\CareRecords;

use App\Models\CarePlan;
use App\Models\CareRecord;
use App\Models\FieldAssignment;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateCareRecord
{
    /**
     * Create a care record (realisasi perawatan) with optional photo evidence.
     * Only field staff can create; they may only record for trees they are assigned to.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): CareRecord
    {
        Gate::forUser($actor)->authorize('create', CareRecord::class);

        if (isset($attributes['action']) && is_string($attributes['action'])) {
            $attributes['action'] = trim($attributes['action']);
        }

        // Normalize nullable plan_id
        if (array_key_exists('plan_id', $attributes) && ($attributes['plan_id'] === '' || $attributes['plan_id'] === null)) {
            $attributes['plan_id'] = null;
        }

        $data = Validator::make($attributes, [
            'tree_id' => ['bail', 'required', 'integer', 'exists:trees,id'],
            'plan_id' => ['nullable', 'integer', 'exists:care_plans,id'],
            'performed_on' => ['bail', 'required', 'date_format:Y-m-d'],
            'action' => ['required', 'string', 'max:1000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'tree_id.required' => 'Pohon wajib dipilih.',
            'tree_id.exists' => 'Pohon yang dipilih tidak valid.',
            'plan_id.exists' => 'Rencana perawatan yang dipilih tidak valid.',
            'performed_on.required' => 'Tanggal pelaksanaan wajib diisi.',
            'performed_on.date_format' => 'Tanggal pelaksanaan harus berupa tanggal yang valid.',
            'action.required' => 'Deskripsi tindakan wajib diisi.',
            'photo.mimes' => 'Bukti foto harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ])->validate();

        // Ownership check: field staff may only record for trees assigned to them.
        $assignedTreeIds = $this->assignedTreeIds($actor);

        if (! in_array((int) $data['tree_id'], $assignedTreeIds, true)) {
            throw ValidationException::withMessages([
                'tree_id' => 'Anda hanya dapat mencatat realisasi untuk pohon yang ditugaskan kepada Anda.',
            ]);
        }

        // Cross-record integrity: if plan_id is given, it must reference the same tree.
        if (! empty($data['plan_id'])) {
            $plan = CarePlan::query()->find($data['plan_id']);

            if ($plan && (int) $plan->tree_id !== (int) $data['tree_id']) {
                throw ValidationException::withMessages([
                    'plan_id' => 'Rencana perawatan yang dipilih tidak sesuai dengan pohon yang dipilih.',
                ]);
            }
        }

        return DB::transaction(function () use ($actor, $data, $attributes): CareRecord {
            $photoPath = null;

            if (isset($attributes['photo']) && $attributes['photo'] instanceof UploadedFile) {
                $photoPath = $attributes['photo']->store('care-records/photos', 'private');
            }

            $record = new CareRecord;
            $record->tree_id = $data['tree_id'];
            $record->plan_id = $data['plan_id'] ?? null;
            $record->performed_by = $actor->getKey();
            $record->performed_on = $data['performed_on'];
            $record->action = $data['action'];
            $record->photo_path = $photoPath;
            $record->status = 'draft';
            $record->save();

            $record->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_record.created',
                'from_status' => null,
                'to_status' => 'draft',
                'metadata' => [
                    'plan_id' => $record->plan_id,
                    'has_photo' => $photoPath !== null,
                ],
            ]);

            return $record;
        });
    }

    /**
     * Return the list of tree IDs currently assigned to this field staff member.
     * Uses field_assignments; returns all assigned tree IDs regardless of assignment status,
     * because M09 may still be in development. If no assignments exist, returns empty.
     *
     * @return list<int>
     */
    private function assignedTreeIds(User $actor): array
    {
        return FieldAssignment::query()
            ->where('assignee_id', $actor->getKey())
            ->pluck('tree_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
