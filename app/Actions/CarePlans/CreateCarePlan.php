<?php

namespace App\Actions\CarePlans;

use App\Models\CarePlan;
use App\Models\FieldAssignment;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateCarePlan
{
    /**
     * Create a care plan and record its initial audit event.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): CarePlan
    {
        Gate::forUser($actor)->authorize('create', CarePlan::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'tree_id',
            'assignment_id',
            'planned_on',
            'action',
        ]));

        if (isset($attributes['action']) && is_string($attributes['action'])) {
            $attributes['action'] = trim($attributes['action']);
        }

        if (array_key_exists('assignment_id', $attributes) && ($attributes['assignment_id'] === '' || $attributes['assignment_id'] === null)) {
            $attributes['assignment_id'] = null;
        }

        $data = Validator::make($attributes, [
            'tree_id' => ['bail', 'required', 'integer', 'exists:trees,id'],
            'assignment_id' => ['nullable', 'integer', 'exists:field_assignments,id'],
            'planned_on' => ['bail', 'required', 'date_format:Y-m-d'],
            'action' => ['required', 'string', 'max:1000'],
        ], [
            'tree_id.required' => 'Pohon wajib dipilih.',
            'tree_id.exists' => 'Pohon yang dipilih tidak valid.',
            'planned_on.required' => 'Tanggal target wajib diisi.',
            'planned_on.date_format' => 'Tanggal target harus berupa tanggal yang valid.',
            'action.required' => 'Aksi perawatan wajib diisi.',
            'assignment_id.exists' => 'Penugasan yang dipilih tidak valid.',
        ])->validate();

        // Cross-record integrity check: if assignment_id is provided and linked to a tree, verify consistency
        if (! empty($data['assignment_id'])) {
            $assignment = FieldAssignment::query()->find($data['assignment_id']);
            if ($assignment && $assignment->tree_id !== null && (int) $assignment->tree_id !== (int) $data['tree_id']) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'Penugasan lapangan tidak sesuai dengan pohon yang dipilih.',
                ]);
            }
        }

        return DB::transaction(function () use ($actor, $data): CarePlan {
            $plan = new CarePlan;
            $plan->tree_id = $data['tree_id'];
            $plan->assignment_id = $data['assignment_id'] ?? null;
            $plan->planned_on = $data['planned_on'];
            $plan->action = $data['action'];
            $plan->status = 'draft';
            $plan->save();

            $plan->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'care_plan.created',
                'from_status' => null,
                'to_status' => 'draft',
                'metadata' => [
                    'assignment_id' => $plan->assignment_id,
                ],
            ]);

            return $plan;
        });
    }
}
