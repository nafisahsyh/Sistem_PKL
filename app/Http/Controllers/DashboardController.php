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
    // =================== DASHBOARD CONTROLLER ===================
    public function index(Request $request)
    {
        // Filter untuk grafik Lahan
        $filterDesa = $request->desa;
        $filterTahun = $request->tahun;

        // Filter untuk grafik Kelola
        $filterDesaKelola = $request->input('desa_kelola');
        $filterTahunKelola = $request->input('tahun_kelola');

        // =================== DATA CARD ===================
        $jumlahKecamatan = Kecamatan::count();
        $jumlahDesa = Desa::count();
        $jumlahPengguna = User::count();
        $jumlahPetani = Petani::count();
        $jumlahLahan = Lahan::whereHas('detailKepemilikan', function ($q) {
            $q->where('status_kepemilikan', 'aktif')
                ->whereHas('kepemilikan.petani', function ($q2) {
                    $q2->where('status', 'aktif');
                });
        })->count();

        $jumlahPetaniAktif = Petani::where('status', 'aktif')->count();
        $jumlahPetaniNonaktif = Petani::whereIn('status', ['tidak_aktif', 'berhenti'])->count();

        $desaList = Desa::orderBy('desa')->get();
        $tahunList = DB::table('tahun_tanam')->orderBy('tahun', 'desc')->get();

        // =================== GRAFIK LAHAN ===================
        $queryLahan = Lahan::select(
            'id_desa',
            'id_tahun_tanam',
            DB::raw('SUM(luas_peta) as total_lapangan'),
            DB::raw('SUM(luas_surat) as total_surat')
        )
            ->leftJoin('detail_kepemilikan', 'lahan.id_lahan', '=', 'detail_kepemilikan.id_lahan')
            ->leftJoin('kepemilikan', 'detail_kepemilikan.id_kepemilikan', '=', 'kepemilikan.id_kepemilikan')
            ->leftJoin('petani', 'kepemilikan.id_petani', '=', 'petani.id_petani')
            ->where('petani.status', 'aktif');

        if ($filterDesa && $filterDesa != "all") {
            $queryLahan->where('lahan.id_desa', $filterDesa);
        }

        if ($filterTahun && $filterTahun != "all") {
            $queryLahan->where('lahan.id_tahun_tanam', $filterTahun);
        }

        $dataLahan = $queryLahan
            ->groupBy('lahan.id_desa', 'lahan.id_tahun_tanam')
            ->with(['desa:id_desa,desa', 'tahunTanam:id_tahun_tanam,tahun'])
            ->get();

        // Tambahan: ambil limit dari request (default 5)
        $limitData = $request->input('limit', 5);

        // Potong data sesuai pilihan dropdown
        if ($limitData != 'all') {
            $dataLahan = $dataLahan->take((int)$limitData);
        }

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
        // =================== PIE CHART JUMLAH PETANI =================
        $statusData = [
            'petani' => [
                'Mandiri' => Petani::whereHas('kepemilikan.detailKepemilikan', function ($q) {
                    $q->where('status_pengelolaan', 'Mandiri')
                        ->where('status_kepemilikan', 'aktif'); // hanya yang aktif
                })
                    ->distinct()
                    ->count('id_petani'),

                'KSM' => Petani::whereHas('kepemilikan.detailKepemilikan', function ($q) {
                    $q->where('status_pengelolaan', 'KSM')
                        ->where('status_kepemilikan', 'aktif'); // hanya yang aktif
                })
                    ->distinct()
                    ->count('id_petani'),
            ],

            // =================== PIE CHART JUMLAH LAHAN =================
            'lahan' => [
                'Mandiri' => Lahan::whereHas('detailKepemilikan', function ($q) {
                    $q->where('status_pengelolaan', 'Mandiri')
                        ->where('status_kepemilikan', 'aktif'); // hanya lahan aktif
                })
                    ->distinct()
                    ->count('id_lahan'),

                'KSM' => Lahan::whereHas('detailKepemilikan', function ($q) {
                    $q->where('status_pengelolaan', 'KSM')
                        ->where('status_kepemilikan', 'aktif'); // hanya lahan aktif
                })
                    ->distinct()
                    ->count('id_lahan'),

                // TOTAL LUAS SURAT → tabel detail_kepemilikan
                'Mandiri_surat' => DB::table('detail_kepemilikan')
                    ->where('status_pengelolaan', 'Mandiri')
                    ->where('status_kepemilikan', 'aktif') // hanya lahan aktif
                    ->sum('luas_surat'),

                'KSM_surat' => DB::table('detail_kepemilikan')
                    ->where('status_pengelolaan', 'KSM')
                    ->where('status_kepemilikan', 'aktif') // hanya lahan aktif
                    ->sum('luas_surat'),

                // TOTAL LUAS PETA → tabel lahan dengan relasi detail_kepemilikan aktif
                'Mandiri_peta' => Lahan::whereHas('detailKepemilikan', function ($q) {
                    $q->where('status_pengelolaan', 'Mandiri')
                        ->where('status_kepemilikan', 'aktif'); // hanya lahan aktif
                })->sum('luas_peta'),

                'KSM_peta' => Lahan::whereHas('detailKepemilikan', function ($q) {
                    $q->where('status_pengelolaan', 'KSM')
                        ->where('status_kepemilikan', 'aktif'); // hanya lahan aktif
                })->sum('luas_peta'),
            ],

        ];

        $pengelolaanQuery = Kepemilikan::select(
            'desa.desa',
            'tahun_tanam.tahun',
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'KSM' THEN 1 ELSE 0 END) as petani_ksm"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'Mandiri' THEN 1 ELSE 0 END) as petani_mandiri"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'KSM' THEN 1 ELSE 0 END) as lahan_ksm"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'Mandiri' THEN 1 ELSE 0 END) as lahan_mandiri"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'KSM' THEN detail_kepemilikan.luas_surat ELSE 0 END) as luas_surat_ksm"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'Mandiri' THEN detail_kepemilikan.luas_surat ELSE 0 END) as luas_surat_mandiri"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'KSM' THEN lahan.luas_peta ELSE 0 END) as luas_peta_ksm"),
            DB::raw("SUM(CASE WHEN detail_kepemilikan.status_pengelolaan = 'Mandiri' THEN lahan.luas_peta ELSE 0 END) as luas_peta_mandiri")
        )
            ->join('detail_kepemilikan', 'kepemilikan.id_kepemilikan', '=', 'detail_kepemilikan.id_kepemilikan')
            ->join('lahan', 'detail_kepemilikan.id_lahan', '=', 'lahan.id_lahan')
            ->join('desa', 'lahan.id_desa', '=', 'desa.id_desa')
            ->join('tahun_tanam', 'lahan.id_tahun_tanam', '=', 'tahun_tanam.id_tahun_tanam');

        // Hanya filter khusus untuk grafik Kelola
        if ($filterDesaKelola && $filterDesaKelola != 'all') {
            $pengelolaanQuery->where('lahan.id_desa', $filterDesaKelola);
        }

        if ($filterTahunKelola && $filterTahunKelola != 'all') {
            $pengelolaanQuery->where('lahan.id_tahun_tanam', $filterTahunKelola);
        }

        $pengelolaanChart = $pengelolaanQuery
            ->groupBy('desa.desa', 'tahun_tanam.tahun')
            ->get()
            ->map(function ($item) {
                return [
                    'desa' => $item->desa,
                    'tahun' => $item->tahun,
                    'petani_ksm' => (int) $item->petani_ksm,
                    'petani_mandiri' => (int) $item->petani_mandiri,
                    'lahan_ksm' => (int) $item->lahan_ksm,
                    'lahan_mandiri' => (int) $item->lahan_mandiri,
                    'luas_surat_ksm' => (float) $item->luas_surat_ksm,
                    'luas_surat_mandiri' => (float) $item->luas_surat_mandiri,
                    'luas_peta_ksm' => (float) $item->luas_peta_ksm,
                    'luas_peta_mandiri' => (float) $item->luas_peta_mandiri,
                ];
            });

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
            'statusData',
            'filterDesaKelola',
            'filterTahunKelola',
            'pengelolaanChart'
        ));
    }
}