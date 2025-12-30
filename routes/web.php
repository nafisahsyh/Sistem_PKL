<?php

use App\Models\InisiasiSaldo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SaldoController;
use App\Http\Controllers\PetaniController;
use App\Http\Controllers\BagiHasilController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\TahunTanamController;
use App\Http\Controllers\KepemilikanController;
use App\Http\Controllers\PengambilanSaldoController;
use App\Http\Controllers\InisiasiSaldoController;


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

    Route::get('/bagi-hasil-bulanan/sisa-saldo', [BagiHasilController::class, 'getSisaSaldo'])
        ->name('bagi-hasil.sisa-saldo');
    Route::get('bagi-hasil-bulanan/total-luas', [BagiHasilController::class, 'getTotalLuas'])->name('bagi-hasil.total-luas');
    Route::post('bagi-hasil-bulanan/store-bulanan', [BagiHasilController::class, 'storeBulanan'])
        ->name('bagi-hasil-bulanan.store-bulanan');
    Route::get('/bagi-hasil-bulanan/{id}/pdf', [BagiHasilController::class, 'detailPdf'])
        ->name('bagi-hasil-bulanan.pdf');
    Route::get('/bagi-hasil-bulanan/pdf', [BagiHasilController::class, 'cetakPdf'])->name('bagi-hasil-bulanan.list-pdf');


    Route::resource('bagi-hasil-bulanan', BagiHasilController::class);
    Route::resource('bagi-periode', BagiHasilController::class);

    Route::get('/pengambilan-saldo', [PengambilanSaldoController::class, 'index'])
        ->name('pengambilan.index');

    Route::get('/pengambilan-saldo/show', [PengambilanSaldoController::class, 'show'])
        ->name('pengambilan.show');
    Route::post('/ambil-saldo', [PengambilanSaldoController::class, 'store'])->name('ambil-saldo.store');
    Route::get('/ambil-saldo/struk/{id_transaksi}', [PengambilanSaldoController::class, 'struk'])->name('ambil-saldo.struk');
    Route::get('/ambil-saldo', [PengambilanSaldoController::class, 'index'])->name('ambil-saldo.index');

    Route::get('/buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
    Route::get('/buku-besar/{id_petani}/{bulan_awal}/{bulan_akhir}', [BukuBesarController::class, 'detail'])->name('buku-besar.detail');
    Route::get('buku-besar/detail-bagi-petani/{id_petani}', [BukuBesarController::class, 'detailBagiPetani'])
        ->name('buku-besar.detail-bagi-petani');
    Route::get('/buku-besar/detail', [BukuBesarController::class, 'detail'])->name('buku-besar.detail');
    Route::get('/buku-besar/pdf', [BukuBesarController::class, 'pdf'])->name('buku-besar.pdf');

    Route::get('/saldo', [SaldoController::class, 'index'])->name('saldo.index');
    Route::get('/saldo/pdf', [SaldoController::class, 'saldoPdf'])->name('saldo.pdf');

    Route::get('/inisiasi-saldo', [InisiasiSaldoController::class, 'index'])
        ->name('inisiasi-saldo.index');
    Route::get('/inisiasi-saldo/create', [InisiasiSaldoController::class, 'create'])
        ->name('inisiasi-saldo.create');
    Route::post('/inisiasi-saldo', [InisiasiSaldoController::class, 'store'])
        ->name('inisiasi-saldo.store');
    Route::post('/inisiasi-saldo/store-petani', [InisiasiSaldoController::class, 'storePetani'])
        ->name('inisiasi-saldo.storePetani');
    Route::delete('/inisiasi-saldo/{saldo}', [InisiasiSaldoController::class, 'destroy'])
        ->name('inisiasi-saldo.destroy');
    Route::put('/inisiasi-saldo/{saldo}', [InisiasiSaldoController::class, 'update'])
        ->name('inisiasi-saldo.update');

});

// hanya super admin
Route::middleware(['auth', 'checkrole:super_admin'])->group(function () {
    Route::resource('user', UserController::class);
});
