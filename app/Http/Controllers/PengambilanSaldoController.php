<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Desa;
use App\Models\Saldo;
use App\Models\Transaksi;
use Nette\Utils\Paginator;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use App\Models\BagiHasilPetani;
use App\Models\BagiHasilBulanan;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PengambilanSaldoController extends Controller
{
    // ===============================
    // INDEX PERIODE (gabungan bulan)
    // ===============================
    public function index(Request $request)
    {
        $query = BagiHasilBulanan::with(['desa', 'tahunTanam']);

        // =========================
        //  FILTER
        // =========================
        if ($request->filled('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        if ($request->filled('id_tahun_tanam')) {
            $query->where('id_tahun_tanam', $request->id_tahun_tanam);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('periode')) {
            $periode = (int) $request->periode;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $query->whereBetween('bulan', [$bulanAwal, $bulanAkhir]);
        }

        // --- Ambil data setelah filter ---
        $bulanan = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'asc')
            ->get();

        // =========================
        // GROUPING PERIODE 2 BULAN
        // =========================
        $periode = $bulanan->groupBy(function ($item) {
            $periodeBulan = ceil($item->bulan / 2);

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

        $nextNumber = DB::table('transaksi')
            ->where('tipe', 'debit_pengambilan')
            ->whereDate('tanggal', $today)
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

        // Reset per hari
        $today = now()->toDateString();

        $nextNumber = DB::table('transaksi')
            ->where('tipe', 'debit_pengambilan')
            ->whereDate('tanggal', $today)
            ->count() + 1;

        return view('pengambilan_saldo.create', [
            'id_bulanan' => $id_bulanan,
            'nextNumber' => $nextNumber,
            'bulan_awal' => $request->bulan_awal,
            'bulan_akhir' => $request->bulan_akhir,
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
        $bulan_awal = $request->bulan_awal;
        $bulan_akhir = $request->bulan_akhir;

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
            'no_bukti' => $request->no_bukti,
            'no_urut' => $request->no_urut,
            'bulan_awal' => $bulan_awal,
            'bulan_akhir' => $bulan_akhir,
        ]);

        $bulanan = BagiHasilBulanan::whereIn('id_bagi_bulanan', $request->id_bulanan)->get();

        $desaIds = $bulanan->pluck('id_desa')->unique();
        $tahunTanamIds = $bulanan->pluck('id_tahun_tanam')->unique();

        Saldo::where('id_petani', $idPetani)
            ->whereIn('id_desa', $desaIds)
            ->whereIn('id_tahun_tanam', $tahunTanamIds)
            ->update(['saldo' => 0]);

        // Redirect ke struk
        return redirect()->route('ambil-saldo.struk', ['id_transaksi' => $transaksi->id_transaksi]);
        
    }


    public function struk($id_transaksi)
    {
        $trx = Transaksi::findOrFail($id_transaksi);

        // =============================
        // Ambil bulan awal & akhir (raw)
        // =============================
        $bulan_awal = $trx->bulan_awal;
        $bulan_akhir = $trx->bulan_akhir;

        // =============================
        // Parse bulan dan tahun
        // =============================
        $bulan_awal_bulan = intval(substr($bulan_awal, 5, 2));
        $bulan_awal_tahun = intval(substr($bulan_awal, 0, 4));

        $bulan_akhir_bulan = intval(substr($bulan_akhir, 5, 2));
        $bulan_akhir_tahun = intval(substr($bulan_akhir, 0, 4));

        $bulananCollection = BagiHasilBulanan::with(['desa', 'tahunTanam'])
            ->where('tahun', $bulan_awal_tahun)
            ->whereBetween('bulan', [$bulan_awal_bulan, $bulan_akhir_bulan])
            ->get();


        $desa = $bulananCollection->first()->desa ?? null;
        $tahunTanam = $bulananCollection->first()->tahunTanam ?? null;

        // Map bulan (angka 1–12) ke id_bagi_bulanan
        $bulanMap = $bulananCollection->pluck('id_bagi_bulanan', 'bulan');

        // Ambil ID berdasarkan bulan angka
        $idBulananAwal = $bulanMap[$bulan_awal_bulan] ?? null;
        $idBulananAkhir = $bulanMap[$bulan_akhir_bulan] ?? null;


        // =============================
        // Ambil data petani untuk 2 bulan itu
        // =============================
        $petaniCollection = BagiHasilPetani::where('id_petani', $trx->id_petani)
            ->whereIn('id_bagi_bulanan', [$idBulananAwal, $idBulananAkhir])
            ->get();

        $luasHa = $petaniCollection
            ->where('id_bagi_bulanan', $idBulananAwal)
            ->sum('total_luas_ksm');

        $p = [
            'id_petani' => $trx->id_petani,
            'nama_petani' => $petaniCollection->first()->nama_petani_snapshot ?? '-',
            'nik_petani' => $petaniCollection->first()->nik_petani_snapshot ?? '-',
            'alamat_petani' => $petaniCollection->first()->alamat_petani_snapshot ?? '-',
            'no_plasma' => $petaniCollection->first()->nomor_plasma_snapshot ?? '-',
            'no_koperasi' => $petaniCollection->first()->nomor_koperasi_snapshot ?? '-',
            'no_urut'        => $trx->no_urut ?? '-',
            'desa' => $desa->desa ?? '-',
            'luas_ha' => $luasHa,
            'nominal' => $petaniCollection->sum('total_nominal'),

            'nominal_bulan_1' => $idBulananAwal
                ? $petaniCollection->where('id_bagi_bulanan', $idBulananAwal)->sum('total_nominal')
                : 0,

            'nominal_bulan_2' => $idBulananAkhir
                ? $petaniCollection->where('id_bagi_bulanan', $idBulananAkhir)->sum('total_nominal')
                : 0,
        ];

        // =============================
        // Nama bulan
        // =============================
        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return view('pengambilan_saldo.nota', [
            'p' => $p,
            'desa' => $desa,
            'tahunTanam' => $tahunTanam,
            'no_bukti' => $trx->no_bukti,
            'trx' => $trx,

            // raw
            'bulan_awal' => $bulan_awal,
            'bulan_akhir' => $bulan_akhir,

            // parsed
            'bulan_awal_bulan' => $bulan_awal_bulan,
            'bulan_awal_tahun' => $bulan_awal_tahun,
            'bulan_akhir_bulan' => $bulan_akhir_bulan,
            'bulan_akhir_tahun' => $bulan_akhir_tahun,

            'bulanNama' => $bulanNama,
        ]);
    }
}
