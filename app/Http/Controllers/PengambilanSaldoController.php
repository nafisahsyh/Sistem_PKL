<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\BagiHasilBulanan;
use App\Models\BagiHasilPetani;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PengambilanSaldoController extends Controller
{
    // ===============================
    // INDEX PERIODE (gabungan bulan)
    // ===============================
    public function index(Request $request)
    {
        $query = BagiHasilBulanan::with(['desa', 'tahunTanam']);

        if ($request->filled('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        if ($request->filled('id_tahun_tanam')) {
            $query->where('id_tahun_tanam', $request->id_tahun_tanam);
        }

        $bulanan = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'asc')
            ->get();

        $periode = $bulanan->groupBy(function ($item) {
            // Hitung periode 2 bulan
            $periodeBulan = ceil($item->bulan / 2); // 1->1, 2->1, 3->2, 4->2, dst
            return $item->tahun . '-' . $item->id_desa . '-' . $item->id_tahun_tanam . '-' . $periodeBulan;
        })->map(function ($group) {
            $bulanAwal = $group->min('bulan');
            $bulanAkhir = $group->max('bulan');
            $totalPeriode = $group->sum('total_bagian');

            return [
                'id_bulanan' => $group->pluck('id_bagi_bulanan')->toArray(),
                'desa' => $group->first()->desa,
                'tahunTanam' => $group->first()->tahunTanam,
                'bulan_awal' => $bulanAwal,
                'bulan_akhir' => $bulanAkhir,
                'tahun' => $group->first()->tahun,
                'tanggal_bagi' => $group->first()->tanggal_bagi,
                'total_periode' => $totalPeriode,
            ];
        })->values();

        // Pagination manual
        $perPage = 20;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $periode->slice($offset, $perPage)->values(),
            $periode->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('pengambilan_saldo.index', [
            'periode' => $paginated,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all()
        ]);
    }


    public function show(Request $request)
    {
        $id_bulanan = $request->id_bulanan ?? [];

        if (empty($id_bulanan)) {
            abort(404, 'Tidak ada data bulanan untuk ditampilkan.');
        }

        // Ambil semua bulan
        $bulananCollection = BagiHasilBulanan::with(['desa', 'tahunTanam'])
            ->whereIn('id_bagi_bulanan', $id_bulanan)
            ->get();

        // Ambil info header gabungan
        $desa = $bulananCollection->first()->desa;
        $tahunTanam = $bulananCollection->first()->tahunTanam;
        $tahun = $bulananCollection->first()->tahun;
        $bulan_awal = $bulananCollection->min('bulan');
        $bulan_akhir = $bulananCollection->max('bulan');
        $total_periode = $bulananCollection->sum('total_bagian');

        // Ambil petani gabungan semua bulan
        $petaniQuery = BagiHasilPetani::whereIn('id_bagi_bulanan', $id_bulanan);

        // FILTER SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $petaniQuery->where(function ($q) use ($search) {
                $q->where('nomor_plasma_snapshot', 'like', "%{$search}%")
                    ->orWhere('nomor_koperasi_snapshot', 'like', "%{$search}%")
                    ->orWhere('nama_petani_snapshot', 'like', "%{$search}%");
            });
        }

        $petaniCollection = $petaniQuery->get();

        // Grouping per petani dan hitung total
        $petaniData = $petaniCollection
            ->groupBy('id_petani')
            ->map(function ($group) {
                $totalLuas = $group
                    ->groupBy(fn($item) => $item->id_desa . '-' . $item->id_tahun_tanam . '-' . $item->id_lahan)
                    ->map(fn($subgroup) => $subgroup->first()->total_luas_ksm)
                    ->sum();

                $totalNominal = $group->sum('total_nominal');

                return [
                    'id_petani' => $group->first()->id_petani,
                    'nama_petani' => $group->first()->nama_petani_snapshot,
                    'nik_petani' => $group->first()->nik_petani_snapshot,
                    'no_plasma' => $group->first()->nomor_plasma_snapshot,
                    'no_koperasi' => $group->first()->nomor_koperasi_snapshot,
                    'luas_ha' => $totalLuas,
                    'nominal' => $totalNominal,
                ];
            })
            ->values();

        // Pagination manual
        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $petaniData->slice($offset, $perPage)->values(),
            $petaniData->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $noStart = ($page - 1) * $perPage + 1;
        $totalLuasHa = $petaniData->sum('luas_ha');

        return view('pengambilan_saldo.show', [
            'desa' => $desa,
            'tahunTanam' => $tahunTanam,
            'tahun' => $tahun,
            'bulan_awal' => $bulan_awal,
            'bulan_akhir' => $bulan_akhir,
            'total_periode' => $total_periode,
            'petaniData' => $paginated,
            'noStart' => $noStart,
            'totalLuasHa' => $totalLuasHa,
        ]);
    }
}
