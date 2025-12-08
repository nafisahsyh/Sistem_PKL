<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        /**
         * 1) Ambil total nominal per petani per periode 2-bulanan
         */
        $subNominal = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("
                CONCAT(
                    bhb.tahun, '-',
                    LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0'),
                    '-01'
                ) AS bulan_awal
            "),
                DB::raw("
                CONCAT(
                    bhb.tahun, '-',
                    LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0'),
                    '-28'
                ) AS bulan_akhir
            "),
                DB::raw('SUM(bhp.total_nominal) AS total_nominal')
            )
            ->when($request->filled('id_desa'), fn($q) => $q->where('bhb.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('bhb.id_tahun_tanam', $request->id_tahun_tanam))
            ->groupBy(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("
                CONCAT(
                    bhb.tahun, '-',
                    LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0'),
                    '-01'
                )
            "),
                DB::raw("
                CONCAT(
                    bhb.tahun, '-',
                    LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0'),
                    '-28'
                )
            ")
            );

        /**
         * 2) Total luas unik per petani (Ha, rounded 2 decimal)
         */
        $subLuas = DB::table('detail_kepemilikan as dk')
            ->join('kepemilikan as k', 'dk.id_kepemilikan', '=', 'k.id_kepemilikan')
            ->join('lahan as l', 'dk.id_lahan', '=', 'l.id_lahan')
            ->select(
                'k.id_petani',
                'l.id_desa',
                'l.id_tahun_tanam',
                DB::raw('ROUND(SUM(l.luas_peta)/10000, 2) AS total_luas_ksm')
            )
            ->where('dk.status_pengelolaan', 'ksm')
            ->where('dk.status_kepemilikan', 'aktif')
            ->groupBy('k.id_petani', 'l.id_desa', 'l.id_tahun_tanam');

        /**
         * 3) Snapshot terakhir per petani
         */
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

        /**
         * 4) Subquery transaksi per petani-desa-tahun-tanam-tipe
         */
        $subTransaksi = DB::table('transaksi as t')
            ->select(
                't.id_petani',
                't.id_desa',
                't.id_tahun_tanam',
                't.tipe',
                DB::raw('SUM(t.nominal) AS total_transaksi')
            )
            ->groupBy('t.id_petani', 't.id_desa', 't.id_tahun_tanam', 't.tipe');

        /**
         * 5) Gabungkan snapshot + luas + nominal + transaksi
         */
        $query = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })
            ->joinSub($subNominal, 'n', function ($join) {
                $join->on('s.id_petani', '=', 'n.id_petani')
                    ->on('s.id_desa', '=', 'n.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'n.id_tahun_tanam');
            })
            ->leftJoinSub($subTransaksi, 't', function ($join) {
                $join->on('s.id_petani', '=', 't.id_petani')
                    ->on('s.id_desa', '=', 't.id_desa')
                    ->on('s.id_tahun_tanam', '=', 't.id_tahun_tanam');
            })
            ->join('desa as d', 's.id_desa', '=', 'd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))
            ->when($request->filled('bulan_awal') && $request->filled('bulan_akhir'), function ($q) use ($request) {
                $q->whereBetween('n.bulan_awal', [
                    $request->bulan_awal . '-01',
                    $request->bulan_akhir . '-01'
                ]);
            })
            ->when($request->filled('bulan_awal') && !$request->filled('bulan_akhir'), function ($q) use ($request) {
                $q->where('n.bulan_awal', $request->bulan_awal . '-01');
            })
            ->when(true, function ($q) use ($request) {
                if ($request->filled('tipe')) {
                    if ($request->tipe == 'credit_bagihasil') {
                        $q->whereIn('t.tipe', ['credit_bagihasil', 'credit_mandiri']);
                    } elseif ($request->tipe == 'debit_pengambilan') {
                        $q->where('t.tipe', 'debit_pengambilan');
                    }
                } else {
                    // default ke tipe kredit kalau belum pilih tipe
                    $q->whereIn('t.tipe', ['credit_bagihasil', 'credit_mandiri']);
                }
            })

            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('s.nama_petani_snapshot', 'like', "%{$search}%")
                        ->orWhere('s.nomor_plasma_snapshot', 'like', "%{$search}%");
                });
            })
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas_ksm AS luasan',
                'd.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'n.bulan_awal',
                'n.bulan_akhir',
                'n.total_nominal'
            )
            ->orderBy('s.id_petani')
            ->paginate(10)
            ->appends($request->query());

        /**
         * 6) Format periode
         */
        $query->getCollection()->transform(function ($trx) use ($namaBulan) {
            $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
            $tahun = substr($trx->bulan_akhir, 0, 4);

            $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
            $trx->total_nominal = (float) $trx->total_nominal;

            return $trx;
        });

        return view('buku_besar.index', [
            'dataTransaksi' => $query,
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
                if (!$bh) continue;

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
}
