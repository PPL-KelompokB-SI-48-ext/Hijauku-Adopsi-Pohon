<?php

namespace App\Actions\FieldAssignments;

use App\Models\Activity;
use App\Models\FieldAssignment;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateFieldAssignment
{
    /**
     * Create a new field assignment with validation and audit log.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): FieldAssignment
    {
        Gate::forUser($actor)->authorize('create', FieldAssignment::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'activity_id',
            'tree_id',
            'assignee_id',
            'due_at',
        ]));

        $data = Validator::make($attributes, [
            'activity_id' => ['required', 'integer', 'exists:activities,id'],
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
            'assignee_id' => ['required', 'integer', 'exists:users,id'],
            'due_at' => ['required', 'date'],
        ], [
            'activity_id.required' => 'Kegiatan lapangan wajib dipilih.',
            'activity_id.exists' => 'Kegiatan yang dipilih tidak valid.',
            'tree_id.required' => 'Pohon wajib dipilih.',
            'tree_id.exists' => 'Pohon yang dipilih tidak valid.',
            'assignee_id.required' => 'Petugas lapangan wajib dipilih.',
            'assignee_id.exists' => 'Petugas yang dipilih tidak valid.',
            'due_at.required' => 'Tenggat penugasan wajib diisi.',
            'due_at.date' => 'Tenggat penugasan harus berupa tanggal dan waktu yang valid.',
        ])->validate();

        // AC 2: Tenggat tidak boleh di masa lalu
        $dueAt = Carbon::parse($data['due_at']);
        if ($dueAt->lt(now()->subMinute())) {
            throw ValidationException::withMessages([
                'due_at' => 'Tenggat penugasan tidak boleh berada di masa lalu.',
            ]);
        }

        // Validasi petugas: wajib role field_staff dan aktif
        $assignee = User::query()->findOrFail($data['assignee_id']);
        if (! $assignee->isFieldStaff()) {
            throw ValidationException::withMessages([
                'assignee_id' => 'Petugas yang dipilih harus memiliki peran petugas lapangan.',
            ]);
        }

        // AC 1: Petugas hanya bisa ditugaskan pada pohon di lokasi kegiatan
        $activity = Activity::query()->findOrFail($data['activity_id']);
        $tree = Tree::query()->with('plot')->findOrFail($data['tree_id']);

        if (! $tree->plot || (int) $tree->plot->site_id !== (int) $activity->site_id) {
            throw ValidationException::withMessages([
                'tree_id' => 'Pohon yang dipilih harus berada di lokasi kegiatan yang sama.',
            ]);
        }

        return DB::transaction(function () use ($actor, $data, $dueAt, $activity, $tree, $assignee): FieldAssignment {
            $assignment = new FieldAssignment();
            $assignment->fill([
                'activity_id' => $activity->getKey(),
                'tree_id' => $tree->getKey(),
                'assignee_id' => $assignee->getKey(),
                'due_at' => $dueAt,
                'status' => 'assigned',
            ]);
            $assignment->save();

            // AC 3: Penugasan tercatat dalam audit log
            $assignment->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'field_assignment.created',
                'to_status' => 'assigned',
                'metadata' => [
                    'activity_id' => $assignment->activity_id,
                    'activity_title' => $activity->title,
                    'tree_id' => $assignment->tree_id,
                    'tree_code' => $tree->code,
                    'assignee_id' => $assignment->assignee_id,
                    'assignee_name' => $assignee->name,
                    'due_at' => $assignment->due_at?->toIso8601String(),
                ],
            ]);

            return $assignment;
        });
    }
}
