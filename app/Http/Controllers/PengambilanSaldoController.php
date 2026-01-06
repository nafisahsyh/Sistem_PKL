<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Desa;
use App\Models\Saldo;
use App\Models\SaldoLalu;
use App\Models\Transaksi;
use Nette\Utils\Paginator;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use App\Models\BagiHasilPetani;
use App\Models\TransaksiDetail;
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

        // =========================
        // AMBIL DATA BULANAN
        // =========================
        $bulanan = $query
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc') // <- ini bikin periode terbaru di atas
            ->get();

        // =========================
        // GROUPING PERIODE 2 BULAN
        // =========================
        $periode = $bulanan
            ->groupBy(function ($item) {
                $periodeBulan = ceil($item->bulan / 2);
                return $item->tahun . '-' . $item->id_desa . '-' . $item->id_tahun_tanam . '-' . $periodeBulan;
            })
            ->filter(fn($group) => $group->count() === 2)
            ->map(function ($group) {

                $bulanAwal = $group->min('bulan'); // bulan ganjil
                $bulanAkhir = $group->max('bulan');
                $bulanGanjil = $group->firstWhere('bulan', $bulanAwal);

                // Ambil saldo periode lalu dari sisa_saldo_snapshot bulan ganjil
                $saldoPeriodeLalu = $bulanGanjil->sisa_saldo_snapshot ?? 0;

                $totalPeriode = $group->sum('total_bagian');
                $totalSaldo = $saldoPeriodeLalu + $totalPeriode;

                return [
                    'id_bulanan' => $group->pluck('id_bagi_bulanan')->toArray(),
                    'desa' => $group->first()->desa,
                    'tahunTanam' => $group->first()->tahunTanam,
                    'bulan_awal' => $bulanAwal,
                    'bulan_akhir' => $bulanAkhir,
                    'tahun' => $group->first()->tahun,
                    'tanggal_bagi' => $bulanGanjil->tanggal_bagi,
                    'total_periode' => $totalPeriode,
                    'saldo_periode_lalu' => $saldoPeriodeLalu,
                    'total_saldo' => $totalSaldo,
                ];
            })
            ->values();


        // =========================
        // PAGINATION MANUAL
        // =========================
        $perPage = 20;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $periode->slice($offset, $perPage)->values(),
            $periode->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]

        );

        return view('pengambilan_saldo.index', [
            'periode' => $paginated,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
        ]);
    }

    public function show(Request $request)
    {
        $id_bulanan = $request->id_bulanan ?? [];

        if (empty($id_bulanan)) {
            abort(404, 'Tidak ada data bulanan untuk ditampilkan.');
        }

        // Ambil semua bulan periode saat ini
        $bulananCollection = BagiHasilBulanan::with(['desa', 'tahunTanam'])
            ->whereIn('id_bagi_bulanan', $id_bulanan)
            ->get();

        $bulanMap = $bulananCollection->pluck('id_bagi_bulanan', 'bulan');

        // Header gabungan
        $desa = $bulananCollection->first()->desa;
        $tahunTanam = $bulananCollection->first()->tahunTanam;
        $tahun = $bulananCollection->first()->tahun;
        $bulan_awal = $bulananCollection->min('bulan') ?? 0;
        $bulan_akhir = $bulananCollection->max('bulan') ?? 0;
        $total_periode = $bulananCollection->sum('total_bagian');

        // ================== SALDO PERIODE LALU (HEADER) ==================
        $bulanGanjil = $bulananCollection
            ->where('bulan', $bulan_awal)
            ->first();

        $saldo_periode_lalu = $bulanGanjil->sisa_saldo_snapshot ?? 0;

        $total_saldo_periode = $total_periode + $saldo_periode_lalu;


        // Ambil semua petani gabungan periode ini
        $petaniQuery = BagiHasilPetani::whereIn('id_bagi_bulanan', $id_bulanan);

        // Filter search
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

        $petaniData = $petaniCollection
            ->groupBy('id_petani')
            ->map(function ($group) use ($bulan_awal, $bulan_akhir, $bulanMap, $tahun) {

                $p = $group->first();
                $id_petani = $p->id_petani;
                $id_desa = $p->id_desa;
                $id_tahun_tanam = $p->id_tahun_tanam;

                // ================== SALDO PER BULAN ==================
                $nominalBulan1 = $group
                    ->where('id_bagi_bulanan', $bulanMap[$bulan_awal] ?? null)
                    ->sum('total_nominal');

                $nominalBulan2 = $group
                    ->where('id_bagi_bulanan', $bulanMap[$bulan_akhir] ?? null)
                    ->sum('total_nominal');

                // ================== TOTAL PERIODE ==================
                $nominalPeriode = $nominalBulan1 + $nominalBulan2;

                // ================== LUAS ==================
                $totalLuas = $group
                    ->groupBy(fn($item) => $item->id_desa . '-' . $item->id_tahun_tanam . '-' . $item->id_lahan)
                    ->map(fn($sub) => $sub->first()->total_luas_ksm)
                    ->sum();

                // ================== SALDO PERIODE LALU ==================
                $saldoPeriodeLalu = SaldoLalu::where('id_petani', $id_petani)
                    ->where('id_desa', $id_desa)
                    ->where('id_tahun_tanam', $id_tahun_tanam)
                    ->where('id_bagi_bulanan', $bulanMap[$bulan_awal] ?? 0)
                    ->sum('saldo_lalu');

                $periodeAwal = sprintf('%04d-%02d', $tahun, $bulan_awal);

                $saldoPeriodeList = Saldo::where('id_petani', $id_petani)
                    ->where('id_desa', $id_desa)
                    ->where('id_tahun_tanam', $id_tahun_tanam)
                    ->where('bulan_awal', '<=', $periodeAwal)
                    ->where('saldo', '>', 0) // ⬅️ KUNCI UTAMA
                    ->orderBy('bulan_awal')
                    ->get()
                    ->map(function ($s) {
                        return [
                            'bulan_awal' => $s->bulan_awal,
                            'bulan_akhir' => $s->bulan_akhir,
                            'saldo' => $s->saldo,
                        ];
                    });

                $pakaiModePeriode = $saldoPeriodeLalu > 0;

                $totalSaldoAkumulasi = $saldoPeriodeList->sum('saldo');

                $periodeBerlanjut = Saldo::where('id_petani', $id_petani)
                    ->where('id_desa', $id_desa)
                    ->where('id_tahun_tanam', $id_tahun_tanam)
                    ->where('bulan_awal', '>', $periodeAwal)
                    ->exists();

                // ================== STATUS ==================
                $sudahDiambil = Transaksi::where('id_petani', $id_petani)
                    ->where('tipe', 'debit_pengambilan')
                    ->where('id_desa', $id_desa)
                    ->where('id_tahun_tanam', $id_tahun_tanam)
                    ->where(function ($q) use ($bulan_awal, $bulan_akhir, $tahun) {
                        $q->where('bulan_awal', '<=', sprintf('%04d-%02d', $tahun, $bulan_akhir))
                            ->where('bulan_akhir', '>=', sprintf('%04d-%02d', $tahun, $bulan_awal));
                    })
                    ->exists();

                $trxTerakhir = Transaksi::where('id_petani', $id_petani)
                    ->where('tipe', 'debit_pengambilan')
                    ->where('id_desa', $id_desa)
                    ->where('id_tahun_tanam', $id_tahun_tanam)
                    ->orderByDesc('tanggal')
                    ->first();

                return [
                    'id_petani' => $id_petani,
                    'nama_petani' => $p->nama_petani_snapshot,
                    'nik_petani' => $p->nik_petani_snapshot,
                    'no_plasma' => $p->nomor_plasma_snapshot,
                    'no_koperasi' => $p->nomor_koperasi_snapshot,
                    'luas_ha' => $totalLuas,
                    'nominal_bulan_1' => $nominalBulan1,
                    'nominal_bulan_2' => $nominalBulan2,
                    'nominal' => $nominalPeriode,
                    'total_saldo_berjalan' => $totalSaldoBerjalan ?? $nominalPeriode,
                    'saldo_periode_lalu' => $saldoPeriodeLalu,
                    'total_hak' => $nominalPeriode + $saldoPeriodeLalu,
                    'saldo_periode_list' => $saldoPeriodeList,
                    'total_saldo_akumulasi' => $totalSaldoAkumulasi,
                    'pakai_mode_periode' => $pakaiModePeriode,
                    'id_desa' => $id_desa,
                    'id_tahun_tanam' => $id_tahun_tanam,
                    'sudah_diambil' => $sudahDiambil,
                    'trx_terakhir' => $trxTerakhir,
                    'periode_berlanjut' => $periodeBerlanjut,
                ];
            })
            ->values();

        // Pagination manual
        $perPage = 15;
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
            'saldo_periode_lalu' => $saldo_periode_lalu,
            'total_saldo_periode' => $total_saldo_periode,
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
            'id_petani'  => 'required',
            'id_bulanan' => 'required|array',
            'metode'     => 'required',
            'no_urut'    => 'required',
            'no_bukti'   => 'required',
            'tanggal'    => 'nullable|date',
        ]);

        $idPetani    = $request->id_petani;
        $bulanAwal   = $request->bulan_awal;   // ex: 2026-01
        $bulanAkhir  = $request->bulan_akhir;  // ex: 2026-04
        $tanggal     = $request->tanggal
            ? Carbon::parse($request->tanggal)
            : now();

        $firstBulanan = BagiHasilBulanan::findOrFail($request->id_bulanan[0]);

        $transaksi = DB::transaction(function () use (
            $request,
            $idPetani,
            $bulanAwal,
            $bulanAkhir,
            $tanggal,
            $firstBulanan
        ) {

            // ================= AMBIL SALDO =================
            $saldoAktif = Saldo::where('id_petani', $idPetani)
                ->where('id_desa', $firstBulanan->id_desa)
                ->where('id_tahun_tanam', $firstBulanan->id_tahun_tanam)
                ->where('saldo', '>', 0)
                ->where('bulan_awal', '<=', $bulanAwal)
                ->orderBy('bulan_awal')
                ->get();

            if ($saldoAktif->isEmpty()) {
                abort(400, 'Tidak ada saldo yang bisa diambil');
            }

            // ================= TOTAL NOMINAL =================
            $totalSaldo = $saldoAktif->sum('saldo');

            // ================= TRANSAKSI =================
            $transaksi = Transaksi::create([
                'id_petani'      => $idPetani,
                'id_desa'        => $firstBulanan->id_desa,
                'id_tahun_tanam' => $firstBulanan->id_tahun_tanam,
                'tipe'           => 'debit_pengambilan',
                'metode'         => $request->metode,
                'nominal'        => $totalSaldo,
                'tanggal'        => $tanggal,
                'keterangan'     => 'Pengambilan saldo periode',
                'no_bukti'       => $request->no_bukti,
                'no_urut'        => $request->no_urut,
                'bulan_awal'     => $saldoAktif->first()->bulan_awal,
                'bulan_akhir'    => $bulanAkhir,
            ]);

            // ================= TRANSAKSI DETAIL (PER PERIODE SALDO) =================
            $details = [];

            foreach ($saldoAktif as $saldo) {
                $details[] = TransaksiDetail::create([
                    'id_transaksi'      => $transaksi->id_transaksi,
                    'periode_awal'      => $saldo->bulan_awal,
                    'periode_akhir'     => $saldo->bulan_akhir,
                    'nominal'           => $saldo->saldo,
                    'nominal_per_bulan' => null, // default
                ]);
            }

            // ================= NOMINAL PER BULAN (HANYA 1 PERIODE & 2 BULAN) =================
            if (count($details) === 1) {

                $detail = $details[0];

                $awal  = Carbon::parse($detail->periode_awal);
                $akhir = Carbon::parse($detail->periode_akhir);

                // HARUS TEPAT 2 BULAN BERURUTAN
                if ($awal->copy()->addMonth()->format('Y-m') === $akhir->format('Y-m')) {

                    $bulanMap = BagiHasilBulanan::whereIn(
                        'id_bagi_bulanan',
                        $request->id_bulanan
                    )->pluck('id_bagi_bulanan', 'bulan')->toArray();

                    $nominalPerBulan = [];

                    foreach ([$awal, $akhir] as $tgl) {
                        $periode = $tgl->format('Y-m');
                        $bulan   = (int) $tgl->format('m');

                        if (!isset($bulanMap[$bulan])) continue;

                        $nominalPerBulan[$periode] =
                            BagiHasilPetani::where('id_petani', $idPetani)
                            ->where('id_desa', $firstBulanan->id_desa)
                            ->where('id_tahun_tanam', $firstBulanan->id_tahun_tanam)
                            ->where('id_bagi_bulanan', $bulanMap[$bulan])
                            ->sum('total_nominal');
                    }

                    $detail->update([
                        'nominal_per_bulan' => $nominalPerBulan
                    ]);
                }
            }

            // ================= RESET SALDO =================
            foreach ($saldoAktif as $saldo) {
                $saldo->update(['saldo' => 0]);
            }

            return $transaksi;
        });

        return redirect()->route('ambil-saldo.struk', [
            'id_transaksi' => $transaksi->id_transaksi
        ]);
    }


    public function struk($id_transaksi)
    {
        $trx = Transaksi::with('details')->findOrFail($id_transaksi);
        $desa = Desa::find($trx->id_desa);
        $petani = BagiHasilPetani::where('id_petani', $trx->id_petani)->first();

        $bulanIndo = function ($bulan) {
            return [
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
            ][$bulan] ?? '-';
        };

        $periods = $trx->details->map(fn($d) => [
            'awal' => $d->periode_awal,
            'akhir' => $d->periode_akhir,
            'nominal' => $d->nominal
        ])->sortBy('awal')->values()->all();


        $periodeList = [];
        $grandTotal = 0;

        if (count($periods) === 1) {

            $detail = $trx->details->first();

            // ================== JIKA ADA RINCIAN PER BULAN ==================
            if (!empty($detail->nominal_per_bulan)) {

                $bulanData = $detail->nominal_per_bulan; // ⬅️ SUDAH ARRAY

                foreach ($bulanData as $periode => $nominal) {
                    $bulan = (int) substr($periode, 5, 2);
                    $tahun = (int) substr($periode, 0, 4);

                    $periodeList[] = [
                        'label'   => 'BULAN ' . $bulanIndo($bulan) . ' ' . $tahun,
                        'nominal' => $nominal
                    ];

                    $grandTotal += $nominal;
                }
            }
        } else {
            // logika lama → splitYears & makeChunks
            $splitYears = [];
            foreach ($periods as $p) {
                $year = (int) substr($p['awal'], 0, 4);
                $splitYears[$year][] = $p;
            }
            ksort($splitYears);

            $hasMultipleYears = count($splitYears) > 1;

            foreach ($splitYears as $year => $yearPeriods) {

                // JIKA LEBIH DARI 1 TAHUN → SETIAP TAHUN JADI SATU BLOK
                if ($hasMultipleYears) {
                    $chunks = [$yearPeriods];
                } else {
                    // HANYA JIKA 1 TAHUN SAJA → boleh dipecah per bulan
                    $chunks = $this->makeChunks($yearPeriods);
                }

                foreach ($chunks as $chunk) {
                    $label = $this->makeLabel($chunk, $bulanIndo);
                    $total = array_sum(array_column($chunk, 'nominal'));

                    $periodeList[] = [
                        'label' => $label,
                        'nominal' => $total
                    ];
                    $grandTotal += $total;
                }
            }
        }


        // ===== Judul gabungan seluruh periode (lintas tahun) =====
        $firstDetail = $trx->details->first();
        $lastDetail = $trx->details->last();
        $firstBulan = (int) substr($firstDetail->periode_awal, 5, 2);
        $firstTahun = (int) substr($firstDetail->periode_awal, 0, 4);
        $lastBulan = (int) substr($lastDetail->periode_akhir, 5, 2);
        $lastTahun = (int) substr($lastDetail->periode_akhir, 0, 4);

        $judulGabungan = ($firstTahun === $lastTahun)
            ? "PERIODE {$bulanIndo($firstBulan)} - {$bulanIndo($lastBulan)} {$firstTahun}"
            : "PERIODE {$bulanIndo($firstBulan)} {$firstTahun} - {$bulanIndo($lastBulan)} {$lastTahun}";

        return view('pengambilan_saldo.nota', [
            'trx' => $trx,
            'periodeList' => $periodeList,
            'periodeGabungan' => $judulGabungan,
            'grandTotal' => $grandTotal,
            'punyaSaldoLalu' => count($periods) > 1,
            'desa' => $desa,
            'tahunTanam' => Tahun_Tanam::find($trx->id_tahun_tanam),
            'no_bukti' => $trx->no_bukti,
            'bulan_awal' => $trx->bulan_awal,
            'bulan_akhir' => $trx->bulan_akhir,
            'p' => [
                'nama_petani' => $petani->nama_petani_snapshot ?? '-',
                'alamat_petani' => $petani->alamat_petani_snapshot ?? '-',
                'desa' => $desa->desa ?? '-',
                'no_plasma' => $petani->nomor_plasma_snapshot ?? '-',
                'no_koperasi' => $petani->nomor_koperasi_snapshot ?? '-',
                'no_urut' => $trx->no_urut,
            ]
        ]);
    }

    // ===== Helper function untuk buat blok maksimal 3 =====
    private function makeChunks(array $yearPeriods)
    {
        $total = count($yearPeriods);
        if ($total >= 6) {
            return array_chunk($yearPeriods, ceil($total / 3));
        } elseif ($total == 5) {
            return [
                array_slice($yearPeriods, 0, 2),
                array_slice($yearPeriods, 2, 2),
                array_slice($yearPeriods, 4, 1)
            ];
        } elseif ($total == 4) {
            return [
                array_slice($yearPeriods, 0, 2),
                array_slice($yearPeriods, 2, 2)
            ];
        } else {
            return array_map(fn($p) => [$p], $yearPeriods);
        }
    }

    // ===== Helper function untuk buat label blok =====
    private function makeLabel(array $block, $bulanIndo)
    {
        $awalBulan = (int) substr($block[0]['awal'], 5, 2);
        $awalTahun = (int) substr($block[0]['awal'], 0, 4);
        $akhirBulan = (int) substr($block[count($block) - 1]['akhir'], 5, 2);
        $akhirTahun = (int) substr($block[count($block) - 1]['akhir'], 0, 4);

        return ($awalTahun === $akhirTahun)
            ? "PERIODE {$bulanIndo($awalBulan)} - {$bulanIndo($akhirBulan)} {$awalTahun}"
            : "PERIODE {$bulanIndo($awalBulan)} {$awalTahun} - {$bulanIndo($akhirBulan)} {$akhirTahun}";
    }
}
