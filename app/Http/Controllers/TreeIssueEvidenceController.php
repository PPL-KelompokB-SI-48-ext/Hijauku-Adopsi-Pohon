<?php

namespace App\Http\Controllers;

use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TreeIssueEvidenceController extends Controller
{
    public function __invoke(Request $request, int $treeIssue): StreamedResponse
    {
        $actor = $request->user();

        abort_unless($actor instanceof User, 403);

        $query = TreeIssue::query();

        if (! $actor->isManager()) {
            $query->reportedBy($actor);
        }

        $issue = $query->findOrFail($treeIssue);

        Gate::forUser($actor)->authorize('downloadEvidence', $issue);

        abort_unless(Storage::disk('private')->exists($issue->evidence_path), 404);

        return Storage::disk('private')->download($issue->evidence_path);
    }
}
