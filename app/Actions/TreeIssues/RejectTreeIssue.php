<?php

namespace App\Actions\TreeIssues;

use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RejectTreeIssue
{
    public function handle(User $actor, TreeIssue $issue, string $reason): TreeIssue
    {
        $reason = trim($reason);

        return DB::transaction(function () use ($actor, $issue, $reason): TreeIssue {
            $lockedIssue = TreeIssue::query()
                ->lockForUpdate()
                ->findOrFail($issue->getKey());

            Gate::forUser($actor)->authorize('reject', $lockedIssue);

            if ($lockedIssue->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'reason' => 'Hanya laporan berstatus dilaporkan yang dapat ditolak.',
                ]);
            }

            $data = Validator::make(['reason' => $reason], [
                'reason' => ['required', 'string', 'max:1000'],
            ], [
                'reason.required' => 'Alasan penolakan wajib diisi.',
                'reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
            ])->validate();

            $lockedIssue->forceFill([
                'status' => 'rejected',
                'reviewer_id' => $actor->getKey(),
                'resolution' => $data['reason'],
            ])->save();

            $lockedIssue->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'tree_issue.rejected',
                'from_status' => 'submitted',
                'to_status' => 'rejected',
                'metadata' => ['reason' => $data['reason']],
            ]);

            return $lockedIssue->refresh();
        });
    }
}
