<?php

namespace App\Http\Controllers;

use App\Models\GrowthObservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrowthObservationPhotoController extends Controller
{
    /**
     * Melayani berkas foto bukti pengamatan pertumbuhan dari disk privat.
     * Otorisasi ditegakkan melalui GrowthObservationPolicy::downloadPhoto.
     */
    public function __invoke(Request $request, GrowthObservation $observation): StreamedResponse
    {
        Gate::authorize('downloadPhoto', $observation);

        abort_if($observation->photo_path === null, 404, 'Tidak ada foto bukti untuk pengamatan ini.');
        abort_unless(Storage::disk('private')->exists($observation->photo_path), 404, 'File foto tidak ditemukan.');

        return Storage::disk('private')->download(
            $observation->photo_path,
            basename($observation->photo_path),
        );
    }
}
