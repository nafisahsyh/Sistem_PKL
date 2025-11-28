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
use App\Http\Controllers\BagiHasilController;



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
    Route::resource('tahun_tanam', TahunTanamController::class);
    Route::resource('desa', DesaController::class);
    Route::resource('petani', PetaniController::class);
    Route::get('/kepemilikan/cetakSemuaPDF', [KepemilikanController::class, 'cetakSemuaPDF'])
        ->name('kepemilikan.cetakSemuaPDF');
    Route::resource('kepemilikan', KepemilikanController::class);
    Route::get('/kepemilikan/{id}/pdf', [KepemilikanController::class, 'cetakPDF'])
        ->name('kepemilikan.pdf');
    Route::get('petani/{id_petani}/createkepemilikan', [PetaniController::class, 'tambahKepemilikan'])
        ->name('petani.createkepemilikan');
    Route::post('/petani/storeKepemilikan', [PetaniController::class, 'storeKepemilikan'])
        ->name('petani.storeKepemilikan');
    Route::get('/kepemilikan/{id_kepemilikan}/lahan/{id_lahan}', [KepemilikanController::class, 'showPerLahan'])
        ->name('kepemilikan.showPerLahan');
    //Per lahan
    Route::get('/kepemilikan/{id_kepemilikan}/lahan/{id_lahan}/edit', [KepemilikanController::class, 'editPerLahan'])
        ->name('kepemilikan.editPerLahan');
    Route::put('/kepemilikan/{id_kepemilikan}/lahan/{id_lahan}/update', [KepemilikanController::class, 'updatePerLahan'])
        ->name('kepemilikan.updatePerLahan');
    Route::get('/kepemilikan/{id_kepemilikan}/lahan/{id_lahan}/pdf', [KepemilikanController::class, 'pdfPerLahan'])
        ->name('kepemilikan.pdfPerLahan');
    Route::delete('/kepemilikan/{id_kepemilikan}/lahan/{id_lahan}', [KepemilikanController::class, 'destroyPerLahan'])
        ->name('kepemilikan.destroyPerLahan');
    Route::get('/kepemilikan/{id_kepemilikan}/detail/{id_detail_kepemilikan}/cetak', [KepemilikanController::class, 'cetakPDFPerLahan'])
        ->name('kepemilikan.cetakPerLahan');
    Route::patch('/pbb/{id_pbb}/lunas', [KepemilikanController::class, 'tandaiLunasPbb'])->name('pbb.lunas');
    Route::post('/pbb/generate/{id_detail_kepemilikan}', [KepemilikanController::class, 'generatePbbTahunBaru'])
        ->name('pbb.generate');
    Route::post('/kepemilikan/{id_kepemilikan}/lahan/{id_lahan}/ganti', [KepemilikanController::class, 'updateKepemilikan'])
        ->name('kepemilikan.updateKepemilikan');
    Route::post('/kepemilikan/update-kepemilikan-semua/{id_kepemilikan}', [KepemilikanController::class, 'updateKepemilikanSemua'])->name('kepemilikan.updateKepemilikanSemua');
    Route::get('/kepemilikan/riwayat-lahan/{id_lahan}', [KepemilikanController::class, 'riwayatLahan'])
        ->name('kepemilikan.riwayatLahan');
    Route::put('/kepemilikan/riwayat/{id}', [KepemilikanController::class, 'updateRiwayat'])
        ->name('riwayat.update');
    Route::delete('/kepemilikan/riwayat/{id}', [KepemilikanController::class, 'deleteRiwayat'])
        ->name('riwayat.destroy');
    Route::get('bagi-hasil-bulanan/total-luas', [BagiHasilController::class, 'getTotalLuas'])->name('bagi-hasil.total-luas');
    Route::post('bagi-hasil-bulanan/store-bulanan', [BagiHasilController::class, 'storeBulanan'])
        ->name('bagi-hasil-bulanan.store-bulanan');
    Route::get('/bagi-hasil-bulanan/{id}/pdf', [BagiHasilController::class, 'detailPdf'])
        ->name('bagi-hasil-bulanan.pdf');
    Route::resource('bagi-hasil-bulanan', BagiHasilController::class);
    Route::resource('bagi-periode', BagiHasilController::class);

});

// hanya super admin
Route::middleware(['auth', 'checkrole:super_admin'])->group(function () {
    Route::resource('user', UserController::class);
});
