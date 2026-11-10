<?php

namespace App\Actions\Programs;

use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArchiveProgram
{
    public function handle(User $actor, Program $program, string $reason): Program
    {
        $reason = trim($reason);

        return DB::transaction(function () use ($actor, $program, $reason): Program {
            $lockedProgram = Program::query()
                ->lockForUpdate()
                ->findOrFail($program->getKey());

            Gate::forUser($actor)->authorize('archive', $lockedProgram);

            Validator::make(['reason' => $reason], [
                'reason' => ['required', 'string', 'max:1000'],
            ], [
                'reason.required' => 'Alasan pengarsipan wajib diisi.',
            ])->validate();

            if ($lockedProgram->status !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya program berstatus selesai yang dapat diarsipkan.',
                ]);
            }

            $lockedProgram->forceFill(['status' => 'archived'])->save();

            $lockedProgram->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'program.archived',
                'from_status' => 'completed',
                'to_status' => 'archived',
                'metadata' => ['reason' => $reason],
            ]);

            return $lockedProgram->refresh();
        });
    }
}
