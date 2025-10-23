<?php

use App\Http\Controllers\KepemilikanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\TahunTanamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetaniController;



Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        switch ($user->role) {
            case 'super_admin':
                return redirect()->route('dashboard.super');
            case 'admin':
                return redirect()->route('dashboard.admin');
            default:
                return redirect('/login');
        }
    }

    return redirect('/login');
});

// Form request reset link
Route::get('/reset', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/reset', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

// Form reset password baru
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

//route auth login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route reset password
Route::get('/reset', function () {
    return view('auth.reset');
});

Route::middleware(['auth', 'checkrole:super_admin,admin'])->group(function () {
    Route::get('/dashboard/super', [DashboardController::class, 'index'])->name('dashboard.super');
    Route::get('/dashboard/admin', [DashboardController::class, 'index'])->name('dashboard.admin');

    // resource yang bisa diakses oleh keduanya
    Route::resource('kecamatan', KecamatanController::class);
    Route::resource('desa', DesaController::class);
    Route::resource('tahun_tanam', TahunTanamController::class);
    Route::resource('petani', PetaniController::class);
    Route::resource('kepemilikan', KepemilikanController::class);
    Route::get('/kepemilikan/{id}/pdf', [KepemilikanController::class, 'cetakPDF'])
        ->name('kepemilikan.pdf');
    Route::get('petani/{id_petani}/createkepemilikan', [PetaniController::class, 'tambahKepemilikan'])
        ->name('petani.createkepemilikan');
    Route::post('petani/simpan-kepemilikan', [PetaniController::class, 'storeKepemilikan'])
        ->name('petani.store_kepemilikan');
});

// hanya super admin
Route::middleware(['auth', 'checkrole:super_admin'])->group(function () {
    Route::resource('user', UserController::class);
});
