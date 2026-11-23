<?php

use App\Http\Controllers\Auth\SharedLoginController;
use App\Http\Controllers\GrowthObservationPhotoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/login', [SharedLoginController::class, 'create'])->name('login');
Route::post('/login', [SharedLoginController::class, 'store'])
    ->middleware('throttle:shared-login')
    ->name('login.store');

Route::redirect('/app/login', '/login')->name('login.alias.app');
Route::redirect('/petugas/login', '/login')->name('login.alias.field-staff');
Route::redirect('/warga/login', '/login')->name('login.alias.citizen');

Route::get('/growth-observations/{observation}/photo', GrowthObservationPhotoController::class)
    ->middleware('auth')
    ->name('growth-observations.photo');
