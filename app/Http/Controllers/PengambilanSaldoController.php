<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\BagiHasilBulanan;
use App\Models\BagiHasilPetani;
use App\Models\Saldo;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;

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

        $bulanMap = $bulananCollection->pluck('id_bagi_bulanan', 'bulan');

        // Ambil info header gabungan
        $desa = $bulananCollection->first()->desa;
        $tahunTanam = $bulananCollection->first()->tahunTanam;
        $tahun = $bulananCollection->first()->tahun;
        $bulan_awal = $bulananCollection->min('bulan') ?? 0;
        $bulan_akhir = $bulananCollection->max('bulan') ?? 0;
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

        $petaniCollection = $petaniQuery
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'id_lahan',
                'id_bagi_bulanan',
                'nama_petani_snapshot',
                'nik_petani_snapshot',
                'nomor_plasma_snapshot',
                'nomor_koperasi_snapshot',
                'total_luas_ksm',
                'total_nominal'
            )
            ->get();

        // Grouping per petani dan hitung total
        $petaniData = $petaniCollection
            ->groupBy('id_petani')
            ->map(function ($group) use ($bulan_awal, $bulan_akhir, $bulanMap) {

                // Hitung total luas
                $totalLuas = $group
                    ->groupBy(fn($item) => $item->id_desa . '-' . $item->id_tahun_tanam . '-' . $item->id_lahan)
                    ->map(fn($subgroup) => $subgroup->first()->total_luas_ksm)
                    ->sum();

                return [
                    'id_petani' => $group->first()->id_petani,
                    'nama_petani' => $group->first()->nama_petani_snapshot,
                    'nik_petani' => $group->first()->nik_petani_snapshot,
                    'no_plasma' => $group->first()->nomor_plasma_snapshot,
                    'no_koperasi' => $group->first()->nomor_koperasi_snapshot,
                    'luas_ha' => $totalLuas,
                    'nominal' => $group->sum('total_nominal'),

                    // Tambahkan saldo per bulan di sini
                    'nominal_bulan_1' => $group
                        ->where('id_bagi_bulanan', $bulanMap[$bulan_awal] ?? 0)
                        ->sum('total_nominal'),

                    'nominal_bulan_2' => $group
                        ->where('id_bagi_bulanan', $bulanMap[$bulan_akhir] ?? 0)
                        ->sum('total_nominal'),

                    'id_desa' => $group->first()->id_desa,
                    'id_tahun_tanam' => $group->first()->id_tahun_tanam,

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

        $today = Carbon::today();

        $nextNumber = \DB::table('transaksi')
            ->where('tipe', 'debit_pengambilan')
            ->whereDate('created_at', $today)
            ->count() + 1;

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
            'id_bulanan' => $id_bulanan,
            'nextNumber' => $nextNumber,
        ]);
    }

    public function create(Request $request)
    {
        $id_bulanan = $request->id_bulanan ?? [];

        if (empty($id_bulanan)) {
            abort(404, 'ID Bulanan tidak ditemukan.');
        }

        // Nomor urut berikutnya
        $nextNumber = \DB::table('transaksi')
            ->where('tipe', 'debit_pengambilan')
            ->max('id_transaksi') + 1;

        return view('pengambilan_saldo.create', [
            'id_bulanan' => $id_bulanan,
            'nextNumber' => $nextNumber,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_petani' => 'required',
            'id_bulanan' => 'required|array',
            'metode' => 'required',
            'no_urut' => 'required',
            'no_bukti' => 'required',
        ]);

        $idPetani = $request->id_petani;

        // Hitung total saldo yang diambil
        $totalSaldo = BagiHasilPetani::whereIn('id_bagi_bulanan', $request->id_bulanan)
            ->where('id_petani', $idPetani)
            ->sum('total_nominal');

        // Simpan transaksi debit
        $transaksi = Transaksi::create([
            'id_petani' => $idPetani,
            'tipe' => 'debit_pengambilan',
            'metode' => $request->metode,
            'nominal' => $totalSaldo,
            'tanggal' => now(),
            'keterangan' => 'Pengambilan saldo periode bulanan',
        ]);

        // Update saldo menjadi 0
        Saldo::where('id_petani', $idPetani)->update([
            'saldo' => 0
        ]);

        // Redirect ke halaman struk
        return redirect()->route('ambil-saldo.struk', [
            'id' => $transaksi->id_transaksi,
            'id_petani' => $idPetani,
            'id_bulanan' => $request->id_bulanan,
        ]);
    }

    public function struk($id_transaksi)
    {
        $trx = Transaksi::findOrFail($id_transaksi);
        $id_petani = $trx->id_petani;

        // Ambil semua id_bulanan yang terkait sebelum transaksi ini
        $id_bulanan = BagiHasilPetani::where('id_petani', $id_petani)
            ->where('created_at', '<=', $trx->created_at)
            ->pluck('id_bagi_bulanan')
            ->toArray();

        if (empty($id_bulanan)) {
            abort(404, 'Data bulanan tidak ditemukan.');
        }

        $bulananCollection = BagiHasilBulanan::with(['desa', 'tahunTanam'])
            ->whereIn('id_bagi_bulanan', $id_bulanan)
            ->get();

        $petaniCollection = BagiHasilPetani::where('id_petani', $id_petani)
            ->whereIn('id_bagi_bulanan', $id_bulanan)
            ->get();

        $bulan_awal = $bulananCollection->min('bulan') ?? 0;
        $bulan_akhir = $bulananCollection->max('bulan') ?? 0;
        $bulanMap = $bulananCollection->pluck('id_bagi_bulanan', 'bulan');

        $p = [
            'id_petani' => $id_petani,
            'nama_petani' => $petaniCollection->first()->nama_petani_snapshot,
            'nik_petani' => $petaniCollection->first()->nik_petani_snapshot,
            'no_plasma' => $petaniCollection->first()->nomor_plasma_snapshot,
            'no_koperasi' => $petaniCollection->first()->nomor_koperasi_snapshot,
            'luas_ha' => $petaniCollection->sum('total_luas_ksm'),
            'nominal' => $petaniCollection->sum('total_nominal'),
            'nominal_bulan_1' => $petaniCollection
                ->where('id_bagi_bulanan', $bulanMap[$bulan_awal] ?? 0)
                ->sum('total_nominal'),
            'nominal_bulan_2' => $petaniCollection
                ->where('id_bagi_bulanan', $bulanMap[$bulan_akhir] ?? 0)
                ->sum('total_nominal'),
        ];

        $desa = $bulananCollection->first()->desa;
        $tahunTanam = $bulananCollection->first()->tahunTanam;
        $nextNumber = $trx->id_transaksi;
        $trx = (object) ['metode' => $trx->metode ?? 'cash'];

        return view('pengambilan_saldo.nota', compact('p', 'desa', 'tahunTanam', 'nextNumber', 'trx', 'bulan_awal', 'bulan_akhir'));
    }

}
