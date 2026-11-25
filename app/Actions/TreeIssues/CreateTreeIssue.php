<?php

namespace App\Actions\TreeIssues;

use App\Models\Tree;
use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CreateTreeIssue
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): TreeIssue
    {
        Gate::forUser($actor)->authorize('create', TreeIssue::class);

        if (isset($attributes['description']) && is_string($attributes['description'])) {
            $attributes['description'] = trim($attributes['description']);
        }

        $data = Validator::make($attributes, [
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
            'description' => ['required', 'string'],
            'severity' => ['required', 'string', Rule::in(array_keys(TreeIssue::SEVERITY_OPTIONS))],
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'tree_id.required' => 'Pohon wajib dipilih.',
            'tree_id.exists' => 'Pohon yang dipilih tidak valid.',
            'description.required' => 'Deskripsi masalah wajib diisi.',
            'severity.required' => 'Tingkat kebutuhan penanganan wajib diisi.',
            'severity.in' => 'Tingkat kebutuhan penanganan tidak valid.',
            'evidence.required' => 'Bukti foto wajib diunggah.',
            'evidence.mimes' => 'Bukti foto harus berformat JPG, PNG, atau WebP.',
            'evidence.max' => 'Ukuran bukti foto maksimal 5 MB.',
        ])->validate();

        $tree = Tree::query()
            ->reportableBy($actor)
            ->find($data['tree_id']);

        if (! $tree) {
            throw ValidationException::withMessages([
                'tree_id' => 'Pohon tidak ditemukan atau tidak dapat dilaporkan oleh akun ini.',
            ]);
        }

        $evidence = $data['evidence'];

        if (! $evidence instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'evidence' => 'Bukti foto tidak valid.',
            ]);
        }

        $evidencePath = $evidence->store('tree-issues/evidence', 'private');

        if (! is_string($evidencePath) || $evidencePath === '') {
            throw new RuntimeException('Bukti foto laporan tidak dapat disimpan.');
        }

        try {
            $issue = new TreeIssue;
            $issue->tree_id = $tree->getKey();
            $issue->reporter_id = $actor->getKey();
            $issue->description = $data['description'];
            $issue->severity = $data['severity'];
            $issue->evidence_path = $evidencePath;
            $issue->status = 'submitted';
            $issue->save();

            return $issue;
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($evidencePath);

            throw $exception;
        }
    }
}
