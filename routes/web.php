<?php

use App\Http\Controllers\Auth\SharedLoginController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/login', [SharedLoginController::class, 'create'])->name('login');
Route::post('/login', [SharedLoginController::class, 'store'])
    ->middleware('throttle:shared-login')
    ->name('login.store');

Route::redirect('/app/login', '/login')->name('login.alias.app');
Route::redirect('/petugas/login', '/login')->name('login.alias.field-staff');
Route::redirect('/warga/login', '/login')->name('login.alias.citizen');
