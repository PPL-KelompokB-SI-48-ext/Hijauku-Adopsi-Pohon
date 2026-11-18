<?php

namespace App\Http\Controllers;

use App\Models\CareRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareRecordPhotoController extends Controller
{
    /**
     * Serve the private photo evidence file for a care record.
     * Authorizes via CareRecordPolicy::downloadPhoto; returns 403 if not permitted.
     */
    public function __invoke(Request $request, CareRecord $record): StreamedResponse
    {
        Gate::authorize('downloadPhoto', $record);

        abort_if($record->photo_path === null, 404, 'Tidak ada foto bukti untuk realisasi ini.');
        abort_unless(Storage::disk('private')->exists($record->photo_path), 404, 'File foto tidak ditemukan.');

        return Storage::disk('private')->download(
            $record->photo_path,
            basename($record->photo_path),
        );
    }
}
