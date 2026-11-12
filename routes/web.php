<?php

use App\Http\Controllers\CareRecordPhotoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Download bukti foto realisasi perawatan (disk privat, otorisasi per record)
Route::middleware(['auth'])->group(function (): void {
    Route::get('/care-records/{record}/photo', CareRecordPhotoController::class)
        ->name('care-records.photo');
});
