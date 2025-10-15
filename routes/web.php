<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\TahunTanamController;

Route::get('/', function () {
    return view('welcome');
});

//route dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('kecamatan', KecamatanController::class);
Route::resource('tahun_tanam', TahunTanamController::class);
Route::resource('desa', DesaController::class);

//route logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');