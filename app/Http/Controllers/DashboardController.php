<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
       // Hitung jumlah data di tabel kecamatan
        $jumlahKecamatan = Kecamatan::count();

        $jumlahDesa = Desa::count();

        $jumlahPengguna = User::count();

        // Kirim data ke view
        return view('dashboard', compact('jumlahKecamatan', 'jumlahDesa', 'jumlahPengguna'));
    }
}
