<?php

namespace App\Actions\Programs;

use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateDraftProgram
{
    /**
     * Create a draft program and its initial audit event.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Program
    {
        Gate::forUser($actor)->authorize('create', Program::class);

        $attributes = array_intersect_key($attributes, array_flip([
            'name',
            'objective',
            'starts_on',
            'ends_on',
        ]));

        foreach (['name', 'objective'] as $field) {
            if (isset($attributes[$field]) && is_string($attributes[$field])) {
                $attributes[$field] = trim($attributes[$field]);
            }
        }

        $data = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string'],
            'starts_on' => ['bail', 'required', 'date_format:Y-m-d'],
            'ends_on' => ['bail', 'required', 'date_format:Y-m-d', 'after:starts_on'],
        ], [
            'name.required' => 'Nama program wajib diisi.',
            'objective.required' => 'Tujuan program wajib diisi.',
            'starts_on.required' => 'Tanggal mulai wajib diisi.',
            'ends_on.required' => 'Tanggal akhir wajib diisi.',
            'starts_on.date_format' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'ends_on.date_format' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'ends_on.after' => 'Tanggal akhir harus setelah tanggal mulai.',
        ])->validate();

        return DB::transaction(function () use ($actor, $data): Program {
            $program = new Program($data);
            $program->forceFill([
                'created_by' => $actor->getKey(),
                'status' => 'draft',
            ]);
            $program->save();

            $program->activityLogs()->create([
                'actor_id' => $actor->getKey(),
                'action' => 'program.created',
                'to_status' => 'draft',
            ]);

            return $program;
        });
    }
}
