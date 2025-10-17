<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\TahunTanamController;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});

//route dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('kecamatan', KecamatanController::class);
Route::resource('tahun_tanam', TahunTanamController::class);
Route::resource('desa', DesaController::class);

//route auth login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/', function () {
        return redirect('/dashboard');
    });
});

Route::get('/reset', function () {
    return view('auth.reset');
});