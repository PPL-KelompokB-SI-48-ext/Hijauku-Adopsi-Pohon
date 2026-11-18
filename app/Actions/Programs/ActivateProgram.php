<?php

namespace App\Actions\Programs;

use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ActivateProgram
{
    public function handle(User $actor, Program $program, string $reason): Program
    {
        $reason = trim($reason);

        return DB::transaction(function () use ($actor, $program, $reason): Program {
            $lockedProgram = Program::query()
                ->lockForUpdate()
                ->findOrFail($program->getKey());

            Gate::forUser($actor)->authorize('activate', $lockedProgram);

            Validator::make(['reason' => $reason], [
                'reason' => ['required', 'string', 'max:1000'],
            ], [
                'reason.required' => 'Alasan aktivasi wajib diisi.',
            ])->validate();

            if ($lockedProgram->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya program berstatus draf yang dapat diaktifkan.',
                ]);
            }

            $attributes = [
                'name' => trim((string) $lockedProgram->name),
                'objective' => trim((string) $lockedProgram->objective),
                'starts_on' => $lockedProgram->starts_on?->format('Y-m-d'),
                'ends_on' => $lockedProgram->ends_on?->format('Y-m-d'),
            ];

            $data = Validator::make($attributes, [
                'name' => ['required', 'string', 'max:255'],
                'objective' => ['required', 'string'],
                'starts_on' => ['bail', 'required', 'date_format:Y-m-d'],
                'ends_on' => ['bail', 'required', 'date_format:Y-m-d', 'after:starts_on'],
            ], [
                'name.required' => 'Nama program wajib diisi sebelum diaktifkan.',
                'objective.required' => 'Tujuan program wajib diisi sebelum diaktifkan.',
                'starts_on.required' => 'Tanggal mulai wajib diisi sebelum diaktifkan.',
                'ends_on.required' => 'Tanggal akhir wajib diisi sebelum diaktifkan.',
                'ends_on.after' => 'Tanggal akhir harus setelah tanggal mulai.',
            ])->validate();

            $lockedProgram->forceFill($data + ['status' => 'active'])->save();

            $lockedProgram->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'program.activated',
                'from_status' => 'draft',
                'to_status' => 'active',
                'metadata' => ['reason' => $reason],
            ]);

            return $lockedProgram->refresh();
        });
    }
}
