<?php

namespace App\Actions\TreeIssues;

use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HandleTreeIssue
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, TreeIssue $issue, array $attributes): TreeIssue
    {
        $attributes = array_intersect_key($attributes, array_flip(['assignee_id']));

        return DB::transaction(function () use ($actor, $issue, $attributes): TreeIssue {
            $lockedIssue = TreeIssue::query()
                ->lockForUpdate()
                ->findOrFail($issue->getKey());

            Gate::forUser($actor)->authorize('handle', $lockedIssue);

            if ($lockedIssue->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya laporan berstatus dilaporkan yang dapat ditangani.',
                ]);
            }

            $data = Validator::make($attributes, [
                'assignee_id' => ['required', 'integer', 'exists:users,id'],
            ], [
                'assignee_id.required' => 'Petugas tindak lanjut wajib dipilih.',
                'assignee_id.exists' => 'Petugas tindak lanjut tidak valid.',
            ])->validate();

            $assignee = User::query()
                ->lockForUpdate()
                ->findOrFail($data['assignee_id']);

            if (! $assignee->isFieldStaff()) {
                throw ValidationException::withMessages([
                    'assignee_id' => 'Petugas tindak lanjut harus memiliki akun petugas aktif.',
                ]);
            }

            $lockedIssue->forceFill([
                'status' => 'in_progress',
                'reviewer_id' => $actor->getKey(),
                'assignee_id' => $assignee->getKey(),
            ])->save();

            $lockedIssue->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'tree_issue.handled',
                'from_status' => 'submitted',
                'to_status' => 'in_progress',
                'metadata' => [
                    'assignee_id' => $assignee->getKey(),
                ],
            ]);

            return $lockedIssue->refresh();
        });
    }
}
