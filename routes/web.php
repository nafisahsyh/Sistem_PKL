<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\TahunTanamController;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('auth.login');
});

//route auth login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route reset password
Route::get('/reset', function () {
    return view('auth.reset');
});

Route::middleware(['auth','checkrole:super_admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('kecamatan', KecamatanController::class);
    Route::resource('tahun_tanam', TahunTanamController::class);
    Route::resource('desa', DesaController::class);  
});
