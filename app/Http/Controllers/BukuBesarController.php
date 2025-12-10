<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        $namaBulan = [
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

        // Ambil snapshot terakhir per petani
        $subSnapshot = DB::table('bagi_hasil_petani as bh1')
            ->join(
                DB::raw('(SELECT id_petani, MAX(id_bagi_petani) AS max_id FROM bagi_hasil_petani GROUP BY id_petani) bh2'),
                function ($join) {
                    $join->on('bh1.id_petani', '=', 'bh2.id_petani')
                        ->on('bh1.id_bagi_petani', '=', 'bh2.max_id');
                }
            )
            ->select(
                'bh1.id_petani',
                'bh1.nama_petani_snapshot',
                'bh1.nomor_plasma_snapshot',
                'bh1.id_desa',
                'bh1.id_tahun_tanam'
            );

        // Ambil total luas per petani
        $subLuas = DB::table('bagi_hasil_petani')
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                DB::raw('SUM(luas_unik) as total_luas')
            )
            ->fromSub(function ($q) {
                $q->from('bagi_hasil_petani')
                    ->select(
                        'id_petani',
                        'id_desa',
                        'id_tahun_tanam',
                        'id_lahan',
                        DB::raw('MAX(total_luas_ksm) as luas_unik')
                    )
                    ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'id_lahan');
            }, 'lahan_unik')
            ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam');


        // Ambil kredit (SUM per periode)
        $subKredit = DB::table('transaksi')
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir',
                DB::raw('SUM(nominal) AS total_nominal')
            )
            ->where('tipe', 'credit_bagihasil');

        // Jika PERIODE & TAHUN diisi
        if ($request->filled('periode') && $request->filled('tahun')) {
            $periode = (int) $request->periode;
            $tahun = $request->tahun;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $bulanAwal = $tahun . '-' . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = $tahun . '-' . str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

            $subKredit->where('bulan_awal', $bulanAwal)
                ->where('bulan_akhir', $bulanAkhir);
        }

        // Jika PERIODE diisi tapi TAHUN tidak
        if ($request->filled('periode') && !$request->filled('tahun')) {
            $periode = (int) $request->periode;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $bulanAwalStr = '-' . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhirStr = '-' . str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

            $subKredit->where('bulan_awal', 'LIKE', "%$bulanAwalStr")
                ->where('bulan_akhir', 'LIKE', "%$bulanAkhirStr");
        }

        // Jika hanya TAHUN diisi
        if ($request->filled('tahun')) {
            $subKredit->where(DB::raw("SUBSTRING(bulan_awal, 1, 4)"), $request->tahun);
        }

        $subKredit->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'bulan_awal', 'bulan_akhir');

        // Ambil debit (per transaksi)
        $subDebit = DB::table('transaksi')
            ->select(
                'id_transaksi',
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir',
                'nominal',
                'metode'
            )
            ->where('tipe', 'debit_pengambilan');

        if ($request->filled('periode') && $request->filled('tahun')) {
            $periode = (int) $request->periode;
            $tahun = $request->tahun;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $bulanAwal = $tahun . '-' . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = $tahun . '-' . str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

            $subDebit->where('bulan_awal', $bulanAwal)
                ->where('bulan_akhir', $bulanAkhir);
        }

        if ($request->filled('periode') && !$request->filled('tahun')) {
            $periode = (int) $request->periode;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $bulanAwalStr = '-' . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhirStr = '-' . str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

            $subDebit->where('bulan_awal', 'LIKE', "%$bulanAwalStr")
                ->where('bulan_akhir', 'LIKE', "%$bulanAkhirStr");
        }

        if ($request->filled('tahun')) {
            $subDebit->where('bulan_awal', 'LIKE', $request->tahun . '%');
        }

        // Gabungkan snapshot + luas + kredit
        $kredit = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })
            ->joinSub($subKredit, 'k', function ($join) {
                $join->on('s.id_petani', '=', 'k.id_petani')
                    ->on('s.id_desa', '=', 'k.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'k.id_tahun_tanam');
            })
            ->join('desa as d', 's.id_desa', '=', 'd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',
                'd.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'k.bulan_awal',
                'k.bulan_akhir',
                'k.total_nominal'
            )
            ->orderBy('s.id_petani')
            ->get()
            ->transform(function ($trx) use ($namaBulan) {
                $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
                $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
                $tahun = substr($trx->bulan_akhir, 0, 4);

                $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
                $trx->metode = '-'; // kredit
                $trx->total_nominal = (float) $trx->total_nominal;

                return $trx;
            });

        // Debit
        $debit = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })
            ->joinSub($subDebit, 'd', function ($join) {
                $join->on('s.id_petani', '=', 'd.id_petani')
                    ->on('s.id_desa', '=', 'd.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'd.id_tahun_tanam');
            })
            ->join('desa as desaTbl', 's.id_desa', '=', 'desaTbl.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',
                'desaTbl.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'd.bulan_awal',
                'd.bulan_akhir',
                'd.nominal',
                'd.metode'
            )
            ->orderBy('s.id_petani')
            ->get()
            ->transform(function ($trx) use ($namaBulan) {
                $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
                $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
                $tahun = substr($trx->bulan_akhir, 0, 4);

                $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
                $trx->total_nominal = (float) $trx->nominal;

                return $trx;
            });

        // Filter search berdasarkan nama_petani atau nomor_plasma
        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $kredit = $kredit->filter(
                fn($trx) =>
                str_contains(strtolower($trx->nama_petani), $search) ||
                str_contains(strtolower($trx->nomor_plasma), $search)
            );

            $debit = $debit->filter(
                fn($trx) =>
                str_contains(strtolower($trx->nama_petani), $search) ||
                str_contains(strtolower($trx->nomor_plasma), $search)
            );
        }

        // Pilih tipe transaksi sesuai filter
        if ($request->tipe == 'debit_pengambilan') {
            $dataTransaksi = $debit;
        } else {
            $dataTransaksi = $kredit;
        }

        // Optional sorting
        $dataTransaksi = $dataTransaksi->sortBy('id_petani');

        $page = request()->get('page', 1);
        $perPage = 10;

        // Buat paginator dari collection
        $dataTransaksi = new LengthAwarePaginator(
            $dataTransaksi->forPage($page, $perPage),
            $dataTransaksi->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view('buku_besar.index', [
            'dataTransaksi' => $dataTransaksi,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
        ]);
    }


    public function detail(Request $request)
    {

        \Carbon\Carbon::setLocale('id');

        $id_petani = $request->id;
        $awal = $request->awal;   // yyyy-mm
        $akhir = $request->akhir; // yyyy-mm

        $periodeAwal = \Carbon\Carbon::parse($awal . '-01');
        $periodeAkhir = \Carbon\Carbon::parse($akhir . '-01');

        // Buat range bulan
        $bulanRange = [];
        $temp = $periodeAwal->copy();
        while ($temp <= $periodeAkhir) {
            $bulanRange[] = $temp->copy();
            $temp->addMonth();
        }

        // Ambil semua record bagi hasil petani (sudah per lahan)
        $bagiHasil = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->where('bhp.id_petani', $id_petani)
            ->select(
                'bhp.id_lahan',
                'bhp.total_luas_ksm',
                'bhp.total_nominal',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan,2,'0'), '-01') AS bulan_awal")
            )
            ->get();

        $tabelData = [];
        $noTabel = 1;
        // Ambil semua lahan unik milik petani ini
        $lahannya = $bagiHasil->groupBy('id_lahan');

        foreach ($bulanRange as $bulanObj) {
            $noLahan = 1; // reset nomor lahan per bulan
            foreach ($lahannya as $id_lahan => $laH) {
                $bh = $laH->firstWhere('bulan_awal', $bulanObj->format('Y-m-d'));
                if (!$bh)
                    continue;

                $tabelData[] = [
                    'no' => $noTabel,       // nomor urut tabel
                    'lahan' => $noLahan,    // nomor lahan per petani
                    'bulan' => $bulanObj->translatedFormat('F Y'),
                    'luas' => $bh->total_luas_ksm,
                    'nominal' => $bh->total_nominal,
                ];
                $noTabel++;
                $noLahan++;
            }
        }


        return view('buku_besar.detail', compact('tabelData'));
    }

    public function pdf(Request $request)
    {
        $namaBulan = [
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

        // Snapshot
        $subSnapshot = DB::table('bagi_hasil_petani as bh1')
            ->join(
                DB::raw('(SELECT id_petani, MAX(id_bagi_petani) AS max_id FROM bagi_hasil_petani GROUP BY id_petani) bh2'),
                function ($join) {
                    $join->on('bh1.id_petani', '=', 'bh2.id_petani')
                        ->on('bh1.id_bagi_petani', '=', 'bh2.max_id');
                }
            )
            ->select(
                'bh1.id_petani',
                'bh1.nama_petani_snapshot',
                'bh1.nomor_plasma_snapshot',
                'bh1.id_desa',
                'bh1.id_tahun_tanam'
            );

        // Luas KSM
        $subLuas = DB::table('bagi_hasil_petani')
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                DB::raw('SUM(luas_unik) as total_luas')
            )
            ->fromSub(function ($q) {
                $q->from('bagi_hasil_petani')
                    ->select(
                        'id_petani',
                        'id_desa',
                        'id_tahun_tanam',
                        'id_lahan',
                        DB::raw('MAX(total_luas_ksm) as luas_unik')
                    )
                    ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'id_lahan');
            }, 'lahan_unik')
            ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam');

        // Kredit
        $subKredit = DB::table('transaksi')
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir',
                DB::raw('SUM(nominal) AS total_nominal')
            )
            ->where('tipe', 'credit_bagihasil');

        // Filter periode/tahun
        if ($request->filled('periode') && $request->filled('tahun')) {
            $periode = (int) $request->periode;
            $tahun = $request->tahun;

            $awal = ($periode - 1) * 2 + 1;
            $akhir = $periode * 2;

            $bulanAwal = $tahun . '-' . str_pad($awal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = $tahun . '-' . str_pad($akhir, 2, '0', STR_PAD_LEFT);

            $subKredit->where('bulan_awal', $bulanAwal)->where('bulan_akhir', $bulanAkhir);
        }

        if ($request->filled('periode') && !$request->filled('tahun')) {
            $periode = (int) $request->periode;
            $awal = ($periode - 1) * 2 + 1;
            $akhir = $periode * 2;

            $bulanAwalStr = '-' . str_pad($awal, 2, '0', STR_PAD_LEFT);
            $bulanAkhirStr = '-' . str_pad($akhir, 2, '0', STR_PAD_LEFT);

            $subKredit->where('bulan_awal', 'LIKE', "%$bulanAwalStr")
                ->where('bulan_akhir', 'LIKE', "%$bulanAkhirStr");
        }

        if ($request->filled('tahun')) {
            $subKredit->where(DB::raw("SUBSTRING(bulan_awal, 1, 4)"), $request->tahun);
        }

        $subKredit->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'bulan_awal', 'bulan_akhir');

        // Debit
        $subDebit = DB::table('transaksi')
            ->select(
                'id_transaksi',
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir',
                'nominal',
                'metode'
            )
            ->where('tipe', 'debit_pengambilan');

        // Filter debit
        if ($request->filled('periode') && $request->filled('tahun')) {
            $periode = (int) $request->periode;
            $tahun = $request->tahun;

            $awal = ($periode - 1) * 2 + 1;
            $akhir = $periode * 2;

            $bulanAwal = $tahun . '-' . str_pad($awal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = $tahun . '-' . str_pad($akhir, 2, '0', STR_PAD_LEFT);

            $subDebit->where('bulan_awal', $bulanAwal)->where('bulan_akhir', $bulanAkhir);
        }

        if ($request->filled('periode') && !$request->filled('tahun')) {
            $periode = (int) $request->periode;

            $awal = ($periode - 1) * 2 + 1;
            $akhir = $periode * 2;

            $subDebit->where('bulan_awal', 'LIKE', '%-' . str_pad($awal, 2, '0', STR_PAD_LEFT))
                ->where('bulan_akhir', 'LIKE', '%-' . str_pad($akhir, 2, '0', STR_PAD_LEFT));
        }

        if ($request->filled('tahun')) {
            $subDebit->where('bulan_awal', 'LIKE', $request->tahun . '%');
        }

        // Kredit gabungan
        $kredit = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })
            ->joinSub($subKredit, 'k', function ($join) {
                $join->on('s.id_petani', '=', 'k.id_petani')
                    ->on('s.id_desa', '=', 'k.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'k.id_tahun_tanam');
            })
            ->join('desa as d', 's.id_desa', '=', 'd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',
                'd.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'k.bulan_awal',
                'k.bulan_akhir',
                'k.total_nominal'
            )
            ->orderBy('s.id_petani')
            ->get()
            ->transform(function ($trx) use ($namaBulan) {
                $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
                $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
                $tahun = substr($trx->bulan_akhir, 0, 4);

                $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
                $trx->metode = '-';
                return $trx;
            });

        // Debit gabungan
        $debit = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })
            ->joinSub($subDebit, 'd', function ($join) {
                $join->on('s.id_petani', '=', 'd.id_petani')
                    ->on('s.id_desa', '=', 'd.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'd.id_tahun_tanam');
            })
            ->join('desa as desaTbl', 's.id_desa', '=', 'desaTbl.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',
                'desaTbl.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'd.bulan_awal',
                'd.bulan_akhir',
                'd.nominal AS total_nominal',
                'd.metode'
            )
            ->orderBy('s.id_petani')
            ->get()
            ->transform(function ($trx) use ($namaBulan) {
                $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
                $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
                $tahun = substr($trx->bulan_akhir, 0, 4);

                $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
                return $trx;
            });

        // Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $kredit = $kredit->filter(
                fn($trx) =>
                str_contains(strtolower($trx->nama_petani), $search) ||
                str_contains(strtolower($trx->nomor_plasma), $search)
            );

            $debit = $debit->filter(
                fn($trx) =>
                str_contains(strtolower($trx->nama_petani), $search) ||
                str_contains(strtolower($trx->nomor_plasma), $search)
            );
        }

        // Pilih tipe
        $dataTransaksi = $request->tipe == 'debit_pengambilan'
            ? $debit
            : $kredit;

        $dataTransaksi = $dataTransaksi->sortBy('id_petani')->values();

        $tipe = trim($request->tipe ?? '');
        $tipe = $tipe === 'debit_pengambilan' ? 'debit' : 'kredit';

        $pdf = Pdf::loadView('buku_besar.pdf', [
            'dataTransaksi' => $dataTransaksi,
            'request' => $request,
            'tipe' => $tipe,
        ])->setPaper('A4', 'landscape');

        //Generate nama file
        $namaFile = "Buku Besar";

        // Tipe transaksi
        $namaFile .= $request->tipe == 'debit_pengambilan'
            ? " Debit"
            : " Kredit";

        // Desa
        if ($request->filled('id_desa')) {
            $desa = Desa::find($request->id_desa)->desa ?? '';
            $namaFile .= " " . $desa;
        }

        // Tahun Tanam
        if ($request->filled('id_tahun_tanam')) {
            $tahunTanam = Tahun_Tanam::find($request->id_tahun_tanam)->tahun ?? '';
            $namaFile .= " " . $tahunTanam;
        }

        $periodeMap = [
            1 => "Jan-Feb",
            2 => "Mar-Apr",
            3 => "Mei-Jun",
            4 => "Jul-Agt",
            5 => "Sep-Okt",
            6 => "Nov-Des",
        ];

        if ($request->filled('periode')) {
            $bulanPeriode = $periodeMap[$request->periode] ?? "Periode: " . $request->periode;
            $namaFile .= " " . $bulanPeriode;
        }

        // Tahun (filter tahun)
        if ($request->filled('tahun')) {
            $namaFile .= " " . $request->tahun;
        }

        $namaFile .= ".pdf";

        return $pdf->download($namaFile);
    }
}
