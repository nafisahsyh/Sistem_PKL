<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\User;
use App\Models\Petani;
use App\Models\Lahan;
use App\models\DetailKepemilikan;
use App\Models\Kepemilikan;

use Illuminate\Support\Facades\DB;

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

        $jumlahPetani = Petani::count();

        $jumlahLahan = Lahan::count();

        // Ambil data luas lapangan dan luas surat per desa dan tahun tanam
        $dataLahan = Lahan::select(
            'id_desa',
            'id_tahun_tanam',
            DB::raw('SUM(luas_peta) as total_lapangan'),
            DB::raw('SUM(luas_surat) as total_surat')
        )
            ->leftJoin('detail_kepemilikan', 'lahan.id_lahan', '=', 'detail_kepemilikan.id_lahan')
            ->groupBy('lahan.id_desa', 'lahan.id_tahun_tanam')
            ->with(['desa:id_desa,desa', 'tahunTanam:id_tahun_tanam,tahun'])
            ->get();

        // Ubah ke format yang mudah untuk Chart.js
        $chartData = [];
        foreach ($dataLahan as $row) {
            $desa = $row->desa->desa ?? '-';
            $tahun = $row->tahunTanam->tahun ?? '-';
            $chartData[] = [
                'desa' => $desa,
                'tahun' => $tahun,
                'lapangan' => round($row->total_lapangan, 2),
                'surat' => round($row->total_surat, 2),
            ];
        }

        // Kirim data ke view
        return view('dashboard', compact(
            'jumlahKecamatan',
            'jumlahDesa',
            'jumlahPengguna',
            'jumlahPetani',
            'jumlahLahan',
            'chartData'
        ));
    }
}
