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
    public function index(Request $request)
    {
        $filterDesa = $request->desa;
        $filterTahun = $request->tahun;

        $jumlahKecamatan = Kecamatan::count();
        $jumlahDesa = Desa::count();
        $jumlahPengguna = User::count();
        $jumlahPetani = Petani::count();

        $jumlahLahan = Lahan::whereHas('detailKepemilikan.kepemilikan.petani', function ($q) {
            $q->where('status', 'aktif');
        })->count();

        $jumlahPetaniAktif = Petani::where('status', 'aktif')->count();
        $jumlahPetaniNonaktif = Petani::whereIn('status', ['tidak_aktif', 'berhenti'])->count();

        //Ambil daftar Desa & Tahun untuk dropdown modal
        $desaList = Desa::orderBy('desa')->get();
        $tahunList = DB::table('tahun_tanam')->orderBy('tahun', 'desc')->get();

        //Query data lahan
        $query = Lahan::select(
            'id_desa',
            'id_tahun_tanam',
            DB::raw('SUM(luas_peta) as total_lapangan'),
            DB::raw('SUM(luas_surat) as total_surat')
        )
            ->leftJoin('detail_kepemilikan', 'lahan.id_lahan', '=', 'detail_kepemilikan.id_lahan')
            ->leftJoin('kepemilikan', 'detail_kepemilikan.id_kepemilikan', '=', 'kepemilikan.id_kepemilikan')
            ->leftJoin('petani', 'kepemilikan.id_petani', '=', 'petani.id_petani')
            ->where('petani.status', '=', 'aktif');

        //Terapkan filter jika ada
        if ($filterDesa && $filterDesa != "all") {
            $query->where('lahan.id_desa', $filterDesa);
        }

        if ($filterTahun && $filterTahun != "all") {
            $query->where('lahan.id_tahun_tanam', $filterTahun);
        }

        $dataLahan = $query
            ->groupBy('lahan.id_desa', 'lahan.id_tahun_tanam')
            ->with(['desa:id_desa,desa', 'tahunTanam:id_tahun_tanam,tahun'])
            ->get();

        // Format untuk chart
        $chartData = [];
        foreach ($dataLahan as $row) {
            $chartData[] = [
                'desa' => $row->desa->desa ?? '-',
                'tahun' => $row->tahunTanam->tahun ?? '-',
                'lapangan' => round($row->total_lapangan, 2),
                'surat' => round($row->total_surat, 2),
            ];
        }

        // Tambahan baru: Data status pengelolaan
        $statusData = [
            'petani' => [
                'Mandiri' => Petani::whereHas('kepemilikan.detailKepemilikan', fn($q)=>$q->where('status_pengelolaan','Mandiri'))
                                    ->distinct()->count('id_petani'),
                'KSM' => Petani::whereHas('kepemilikan.detailKepemilikan', fn($q)=>$q->where('status_pengelolaan','KSM'))
                                ->distinct()->count('id_petani'),
            ],

            'lahan' => [
                // JUMLAH LAHAN
                'Mandiri' => Lahan::whereHas('detailKepemilikan', fn($q)=>$q->where('status_pengelolaan','Mandiri'))
                                ->distinct()->count('id_lahan'),
                'KSM' => Lahan::whereHas('detailKepemilikan', fn($q)=>$q->where('status_pengelolaan','KSM'))
                            ->distinct()->count('id_lahan'),

                // ✅ TOTAL LUAS SURAT → lewat tabel detail_kepemilikan
                'Mandiri_surat' => DB::table('detail_kepemilikan')
                                        ->where('status_pengelolaan','Mandiri')
                                        ->sum('luas_surat'),
                'KSM_surat' => DB::table('detail_kepemilikan')
                                        ->where('status_pengelolaan','KSM')
                                        ->sum('luas_surat'),

                // ✅ TOTAL LUAS PETA / LAPANGAN → lewat tabel lahan
                'Mandiri_peta' => Lahan::whereHas('detailKepemilikan', fn($q)=>$q->where('status_pengelolaan','Mandiri'))
                                        ->sum('luas_peta'),
                'KSM_peta' => Lahan::whereHas('detailKepemilikan', fn($q)=>$q->where('status_pengelolaan','KSM'))
                                    ->sum('luas_peta'),
            ],
        ];


        return view('dashboard', compact(
            'jumlahKecamatan',
            'jumlahDesa',
            'jumlahPengguna',
            'jumlahPetani',
            'jumlahLahan',
            'jumlahPetaniAktif',
            'jumlahPetaniNonaktif',
            'chartData',
            'desaList',
            'tahunList',
            'filterDesa',
            'filterTahun',
            'statusData' //dikirim ke view
        ));
    }
}