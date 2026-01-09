<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SaldoController extends Controller
{
    public function index(Request $request)
    {

        // ================= AUTO FILTER DEFAULT (HANYA PERTAMA KALI) =================
        if (empty($request->query())) {

            $default = DB::table('bagi_hasil_bulanan')
                ->select(
                    'tahun',
                    DB::raw('CEIL(bulan / 2) as periode'),
                    'id_desa',
                    'id_tahun_tanam'
                )
                ->orderByDesc('tahun')
                ->orderByDesc('bulan')
                ->first();

            if ($default) {
                $request->merge([
                    'tahun' => $default->tahun,
                    'periode' => $default->periode,
                    'id_desa' => $default->id_desa,
                    'id_tahun_tanam' => $default->id_tahun_tanam,
                ]);
            }
        }

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

        $subSnapshot = DB::table('bagi_hasil_petani as bh1')
            ->join(
                DB::raw("(
            SELECT id_petani, id_desa, id_tahun_tanam, MAX(id_bagi_petani) AS max_id
            FROM bagi_hasil_petani
            GROUP BY id_petani, id_desa, id_tahun_tanam
        ) bh2"),
                function ($join) {
                    $join->on('bh1.id_petani', '=', 'bh2.id_petani')
                        ->on('bh1.id_desa', '=', 'bh2.id_desa')
                        ->on('bh1.id_tahun_tanam', '=', 'bh2.id_tahun_tanam')
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


        $subLuas = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhp.id_desa',
                'bhp.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan, 2, '0')) as bulan_formatted"),
                DB::raw('SUM(bhp.total_luas_ksm) as total_luas')
            );

        if ($request->filled('tahun')) {
            $subLuas->where('bhb.tahun', $request->tahun);
        }

        if ($request->filled('periode')) {
            $p = (int) $request->periode;
            $bulanAwal = ($p - 1) * 2 + 1;
            $bulanAkhir = $p * 2;

            $subLuas->whereBetween('bhb.bulan', [$bulanAwal, $bulanAkhir]);
        }

        $subLuas->groupBy(
            'bhp.id_petani',
            'bhp.id_desa',
            'bhp.id_tahun_tanam',
            'bhb.tahun',
            'bhb.bulan'
        );

        $subNominal = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw('CEIL(bhb.bulan / 2) as periode'),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0'), '-01') AS bulan_awal"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0'), '-28') AS bulan_akhir"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0')) AS bulan_awal_short"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0')) AS bulan_akhir_short"),
                DB::raw('SUM(bhp.total_nominal) AS total_nominal')
            )
            ->groupBy(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw('periode'),
                DB::raw("bulan_awal"),
                DB::raw("bulan_akhir"),
                DB::raw("bulan_awal_short"),
                DB::raw("bulan_akhir_short")
            );

        $subDebit = DB::table('transaksi_detail as td')
            ->join('transaksi as t', 'td.id_transaksi', '=', 't.id_transaksi')
            ->where('t.tipe', 'debit_pengambilan')
            ->select(
                't.id_petani',
                't.id_desa',
                't.id_tahun_tanam',
                DB::raw("LEFT(td.periode_awal, 7) as periode_awal_short"),
                DB::raw("LEFT(td.periode_akhir, 7) as periode_akhir_short"),
                DB::raw('SUM(td.nominal) AS nominal_debit'),
                DB::raw('MAX(t.metode) AS metode')
            )
            ->groupBy('t.id_petani', 't.id_desa', 't.id_tahun_tanam', 'periode_awal_short', 'periode_akhir_short');

        $subSaldoLalu = DB::table('saldo_lalu as sl')
            ->join('bagi_hasil_bulanan as bhb', 'sl.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'sl.id_petani',
                'sl.id_desa',
                'sl.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan, 2, '0')) AS bulan_awal_short"),
                DB::raw('SUM(sl.saldo_lalu) as saldo_lalu')
            )
            ->groupBy(
                'sl.id_petani',
                'sl.id_desa',
                'sl.id_tahun_tanam',
                DB::raw("bulan_awal_short")
            );

        // ------------------ QUERY UTAMA (join pake short fields) ------------------
        $query = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            // JOIN subNominal
            ->joinSub($subNominal, 'n', function ($join) {
                $join->on('s.id_petani', '=', 'n.id_petani')
                    ->on('s.id_desa', '=', 'n.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'n.id_tahun_tanam');
            })

            ->leftJoinSub($subSaldoLalu, 'sl', function ($join) {
                $join->on('s.id_petani', '=', 'sl.id_petani')
                    ->on('s.id_desa', '=', 'sl.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'sl.id_tahun_tanam')
                    ->on('n.bulan_awal_short', '=', 'sl.bulan_awal_short');
            })

            // Baru JOIN subLuas yang pakai bulan_awal_short
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam')
                    ->on(
                        DB::raw("LEFT(n.bulan_awal, 7)"),
                        '=',
                        'l.bulan_formatted'
                    );
            })

            ->leftJoinSub($subDebit, 'dpt', function ($join) {
                $join->on('s.id_petani', '=', 'dpt.id_petani')
                    ->on('s.id_desa', '=', 'dpt.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'dpt.id_tahun_tanam')
                    ->on('n.bulan_awal_short', '=', 'dpt.periode_awal_short')
                    ->on('n.bulan_akhir_short', '=', 'dpt.periode_akhir_short');
            })

            ->join('desa as dd', 's.id_desa', '=', 'dd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',
                'dd.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'n.bulan_awal',
                'n.bulan_akhir',
                'n.total_nominal',
                DB::raw('COALESCE(sl.saldo_lalu, 0) as saldo_lalu'),
                'dpt.metode',
                'dpt.nominal_debit'
            );
        // ---------------------- FILTER ----------------------
        // Desa
        if ($request->filled('id_desa')) {
            $query->where('s.id_desa', $request->id_desa);
        }

        // Tahun tanam
        if ($request->filled('id_tahun_tanam')) {
            $query->where('s.id_tahun_tanam', $request->id_tahun_tanam);
        }

        // Periode (1–6)
        if ($request->filled('periode')) {
            $periode = (int) $request->periode;
            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $bulanAwal + 1;

            $bulanAwal = str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

            $query->where(DB::raw("SUBSTR(n.bulan_awal, 6, 2)"), $bulanAwal)
                ->where(DB::raw("SUBSTR(n.bulan_akhir, 6, 2)"), $bulanAkhir);
        }

        // Tahun
        if ($request->filled('tahun')) {
            $query->where(DB::raw("SUBSTR(n.bulan_awal, 1, 4)"), $request->tahun);
        }

        // Metode
        if ($request->filled('metode')) {
            if ($request->metode === 'belum') {
                $query->whereNull('dpt.nominal_debit');
            } else {
                $query->where('dpt.metode', $request->metode);
            }
        }

        // SEARCH (nama / nomor plasma)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('s.nama_petani_snapshot', 'LIKE', "%$search%")
                    ->orWhere('s.nomor_plasma_snapshot', 'LIKE', "%$search%");
            });
        }

        $queryStat = clone $query;

        $results = $query
            ->orderBy('n.bulan_awal', 'desc')
            ->orderBy('s.id_petani')
            ->paginate(20)
            ->appends($request->query());

        $dataAll = $queryStat->get();

        $multiPeriode = !$request->filled('periode');

        if ($multiPeriode) {
            // Ambil 1 baris TERAKHIR per petani
            $dataStat = $dataAll
                ->groupBy('id_petani')
                ->map(function ($group) {
                    return $group->sortByDesc('bulan_awal')->first();
                })
                ->values();
        } else {
            // Periode tunggal → aman
            $dataStat = $dataAll;
        }

        $dataAll->transform(function ($row) {

            $row->total_hak = round(
                ($row->total_nominal ?? 0) + ($row->saldo_lalu ?? 0),
                2
            );

            $row->sisa = is_null($row->nominal_debit)
                ? $row->total_hak
                : 0;

            return $row;
        });

        $totalPetani = $dataStat->count();

        $totalSudah = $dataStat->whereNotNull('nominal_debit')->count();
        $totalBelum = $dataStat->whereNull('nominal_debit')->count();

        // ================= CASH =================
        $cash = $dataStat
            ->where('metode', 'cash');

        $jumlahCash = $cash->count();
        $nominalCash = $cash->sum(function ($row) {
            return ($row->saldo_lalu ?? 0) + ($row->total_nominal ?? 0);
        });

        // ================= TRANSFER =================
        $transfer = $dataStat
            ->where('metode', 'transfer');

        $jumlahTransfer = $transfer->count();
        $nominalTransfer = $transfer->sum(function ($row) {
            return ($row->saldo_lalu ?? 0) + ($row->total_nominal ?? 0);
        });

        $totalNominal = $dataStat->sum('total_hak');

        $totalSisa = $dataStat->sum('sisa');

        $stat = [
            'total_petani' => $totalPetani,
            'total_sudah' => $totalSudah,
            'total_belum' => $totalBelum,
            'jumlah_cash' => $jumlahCash,
            'nominal_cash' => $nominalCash,
            'jumlah_transfer' => $jumlahTransfer,
            'nominal_transfer' => $nominalTransfer,
            'total_nominal' => $totalNominal,
            'sisa' => $totalSisa,
        ];

        $rekapTahunan = $dataAll
            // Kelompokkan per tahun
            ->groupBy('tahun_tanam')
            ->map(function ($tahunGroup, $tahun) {

                // Di dalam 1 tahun, ambil saldo TERAKHIR per petani
                $perPetaniTerakhir = $tahunGroup
                    ->groupBy('id_petani')
                    ->map(function ($petaniGroup) {
                    return $petaniGroup
                        ->sortByDesc('bulan_awal')
                        ->first();
                });

                // Rekap tahunan = jumlah saldo terakhir tiap petani
                return (object) [
                    'tahun_tanam' => $tahun,
                    'total_nominal' => $perPetaniTerakhir->sum('total_hak'),
                    'sisa' => $perPetaniTerakhir->sum('sisa'),
                ];
            })
            ->values();

        // filter TAHUN (misalnya 2025)
        if ($request->filled('tahun')) {
            $rekapTahunan->where('bhb.tahun', $request->tahun);
        }

        // filter PERIODE (2 bulanan)
        if ($request->filled('periode')) {
            $p = (int) $request->periode;
            $bulanAwal = ($p - 1) * 2 + 1;
            $bulanAkhir = $bulanAwal + 1;

            $rekapTahunan = $dataAll
                ->groupBy('tahun_tanam')
                ->map(function ($group, $tahun) {
                    $first = $group->first(); // ambil row pertama untuk bulan_awal
                    return (object) [
                        'tahun_tanam' => $tahun,
                        'total_nominal' => $group->sum('total_nominal'),
                        'sisa' => $group->sum('sisa'),
                        'bulan_awal' => $first->bulan_awal, // tambahkan ini
                        'bulan_akhir' => $first->bulan_akhir, // tambahkan ini
                    ];
                })
                ->values();
        }


        $results->getCollection()->transform(function ($row) use ($namaBulan) {
            $bulanAwal = (int) substr($row->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($row->bulan_akhir, 5, 2);
            $tahun = substr($row->bulan_awal, 0, 4);

            $row->periode = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";

            $nominal = $row->total_nominal ?? 0;
            $saldoLalu = $row->saldo_lalu ?? 0;
            $debit = $row->nominal_debit ?? 0;

            $row->total_hak = round($nominal + $saldoLalu, 2);
            $row->sisa = $debit > 0 ? 0 : $row->total_hak;

            // STATUS
            $row->status_metode = $debit > 0
                ? $row->metode
                : 'Belum diambil';

            return $row;
        });

        $hanyaFilterTahun =
            $request->filled('tahun')
            && !$request->filled('id_desa')
            && !$request->filled('id_tahun_tanam')
            && !$request->filled('periode')
            && !$request->filled('metode');


        return view('saldo.index', [
            'dataSaldo' => $results,
            'stat' => $stat,
            'rekapTahunan' => $rekapTahunan,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
            'hanyaFilterTahun' => $hanyaFilterTahun,
        ]);
    }

    public function saldoPdf(Request $request)
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

        $subSnapshot = DB::table('bagi_hasil_petani as bh1')
            ->join(
                DB::raw("(
            SELECT id_petani, id_desa, id_tahun_tanam, MAX(id_bagi_petani) AS max_id
            FROM bagi_hasil_petani
            GROUP BY id_petani, id_desa, id_tahun_tanam
        ) bh2"),
                function ($join) {
                    $join->on('bh1.id_petani', '=', 'bh2.id_petani')
                        ->on('bh1.id_desa', '=', 'bh2.id_desa')
                        ->on('bh1.id_tahun_tanam', '=', 'bh2.id_tahun_tanam')
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


        $subLuas = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhp.id_desa',
                'bhp.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan, 2, '0')) as bulan_formatted"),
                DB::raw('SUM(bhp.total_luas_ksm) as total_luas')
            )
            ->groupBy(
                'bhp.id_petani',
                'bhp.id_desa',
                'bhp.id_tahun_tanam',
                'bhb.tahun',
                'bhb.bulan'
            );

        $subNominal = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0'), '-01') AS bulan_awal"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0'), '-28') AS bulan_akhir"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0')) AS bulan_awal_short"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0')) AS bulan_akhir_short"),
                DB::raw('SUM(bhp.total_nominal) AS total_nominal')
            )
            ->groupBy(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("bulan_awal"),
                DB::raw("bulan_akhir"),
                DB::raw("bulan_awal_short"),
                DB::raw("bulan_akhir_short")
            );

        $subDebit = DB::table('transaksi_detail as td')
            ->join('transaksi as t', 'td.id_transaksi', '=', 't.id_transaksi')
            ->where('t.tipe', 'debit_pengambilan')
            ->select(
                't.id_petani',
                't.id_desa',
                't.id_tahun_tanam',
                DB::raw("LEFT(td.periode_awal, 7) as periode_awal_short"),
                DB::raw("LEFT(td.periode_akhir, 7) as periode_akhir_short"),
                DB::raw('SUM(td.nominal) AS nominal_debit'),
                DB::raw('MAX(t.metode) AS metode')
            )
            ->groupBy('t.id_petani', 't.id_desa', 't.id_tahun_tanam', 'periode_awal_short', 'periode_akhir_short');

        $subSaldoLalu = DB::table('saldo_lalu as sl')
            ->join('bagi_hasil_bulanan as bhb', 'sl.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'sl.id_petani',
                'sl.id_desa',
                'sl.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan, 2, '0')) AS bulan_awal_short"),
                DB::raw('SUM(sl.saldo_lalu) as saldo_lalu')
            )
            ->groupBy(
                'sl.id_petani',
                'sl.id_desa',
                'sl.id_tahun_tanam',
                DB::raw("bulan_awal_short")
            );

        // ------------------ QUERY UTAMA (join pake short fields) ------------------
        $query = DB::table(DB::raw("(" . $subSnapshot->toSql() . ") as s"))
            ->mergeBindings($subSnapshot)
            // JOIN subNominal
            ->joinSub($subNominal, 'n', function ($join) {
                $join->on('s.id_petani', '=', 'n.id_petani')
                    ->on('s.id_desa', '=', 'n.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'n.id_tahun_tanam');
            })

            ->leftJoinSub($subSaldoLalu, 'sl', function ($join) {
                $join->on('s.id_petani', '=', 'sl.id_petani')
                    ->on('s.id_desa', '=', 'sl.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'sl.id_tahun_tanam')
                    ->on('n.bulan_awal_short', '=', 'sl.bulan_awal_short');
            })

            // Baru JOIN subLuas yang pakai bulan_awal_short
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam')
                    ->on('l.bulan_formatted', '=', 'n.bulan_awal_short');
            })
            ->leftJoinSub($subDebit, 'dpt', function ($join) {
                $join->on('s.id_petani', '=', 'dpt.id_petani')
                    ->on('s.id_desa', '=', 'dpt.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'dpt.id_tahun_tanam')
                    ->on('n.bulan_awal_short', '=', 'dpt.periode_awal_short')
                    ->on('n.bulan_akhir_short', '=', 'dpt.periode_akhir_short');
            })

            ->join('desa as dd', 's.id_desa', '=', 'dd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',
                'dd.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'n.bulan_awal',
                'n.bulan_akhir',
                'n.total_nominal',
                DB::raw('COALESCE(sl.saldo_lalu, 0) as saldo_lalu'),
                'dpt.metode',
                'dpt.nominal_debit'
            );
        // ---------------------- FILTER ----------------------
        // Desa
        if ($request->filled('id_desa')) {
            $query->where('s.id_desa', $request->id_desa);
        }

        // Tahun tanam
        if ($request->filled('id_tahun_tanam')) {
            $query->where('s.id_tahun_tanam', $request->id_tahun_tanam);
        }

        // Periode (1–6)
        if ($request->filled('periode')) {
            $periode = (int) $request->periode;
            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $bulanAwal + 1;

            $bulanAwal = str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

            $query->where(DB::raw("SUBSTR(n.bulan_awal, 6, 2)"), $bulanAwal)
                ->where(DB::raw("SUBSTR(n.bulan_akhir, 6, 2)"), $bulanAkhir);
        }

        // Tahun
        if ($request->filled('tahun')) {
            $query->where(DB::raw("SUBSTR(n.bulan_awal, 1, 4)"), $request->tahun);
        }

        // Metode
        if ($request->filled('metode')) {
            if ($request->metode === 'belum') {
                $query->whereNull('dpt.nominal_debit');
            } else {
                $query->where('dpt.metode', $request->metode);
            }
        }

        // SEARCH (nama / nomor plasma)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('s.nama_petani_snapshot', 'LIKE', "%$search%")
                    ->orWhere('s.nomor_plasma_snapshot', 'LIKE', "%$search%");
            });
        }

        $queryStat = clone $query;

        $results = $query
            ->orderBy('n.bulan_awal', 'desc')
            ->orderBy('s.id_petani')
            ->get();

        $dataAll = $queryStat->get();

        $multiPeriode = !$request->filled('periode');

        if ($multiPeriode) {
            // Ambil 1 baris TERAKHIR per petani
            $dataStat = $dataAll
                ->groupBy('id_petani')
                ->map(function ($group) {
                    return $group->sortByDesc('bulan_awal')->first();
                })
                ->values();
        } else {
            // Periode tunggal → aman
            $dataStat = $dataAll;
        }

        $dataAll->transform(function ($row) {
            $row->total_hak = round(
                ($row->total_nominal ?? 0) + ($row->saldo_lalu ?? 0),
                2
            );

            $row->sisa = is_null($row->nominal_debit)
                ? $row->total_hak
                : 0;

            return $row;
        });

        $totalPetani = $dataStat->count();

        $totalSudah = $dataStat->whereNotNull('nominal_debit')->count();
        $totalBelum = $dataStat->whereNull('nominal_debit')->count();

        // ================= CASH =================
        $cash = $dataStat
            ->where('metode', 'cash')
            ->whereNotNull('nominal_debit');

        $jumlahCash = $cash->count();
        $nominalCash = $cash->sum(function ($row) {
            return ($row->saldo_lalu ?? 0) + ($row->total_nominal ?? 0);
        });

        // ================= TRANSFER =================
        $transfer = $dataStat
            ->where('metode', 'transfer')
            ->whereNotNull('nominal_debit');

        $jumlahTransfer = $transfer->count();
        $nominalTransfer = $transfer->sum(function ($row) {
            return ($row->saldo_lalu ?? 0) + ($row->total_nominal ?? 0);
        });

        $totalNominal = $dataStat->sum('total_hak');
        $totalSisa = $dataStat->sum('sisa');

        $stat = [
            'total_petani' => $totalPetani,
            'total_sudah' => $totalSudah,
            'total_belum' => $totalBelum,
            'jumlah_cash' => $jumlahCash,
            'nominal_cash' => $nominalCash,
            'jumlah_transfer' => $jumlahTransfer,
            'nominal_transfer' => $nominalTransfer,
            'total_nominal' => $totalNominal,
            'sisa' => $totalSisa,
        ];

        $rekapTahunan = $dataAll
            // Kelompokkan per tahun
            ->groupBy('tahun_tanam')
            ->map(function ($tahunGroup, $tahun) {

                // Di dalam 1 tahun, ambil saldo TERAKHIR per petani
                $perPetaniTerakhir = $tahunGroup
                    ->groupBy('id_petani')
                    ->map(function ($petaniGroup) {
                    return $petaniGroup
                        ->sortByDesc('bulan_awal')
                        ->first();
                });

                // Rekap tahunan = jumlah saldo terakhir tiap petani
                return (object) [
                    'tahun_tanam' => $tahun,
                    'total_nominal' => $perPetaniTerakhir->sum('total_hak'),
                    'sisa' => $perPetaniTerakhir->sum('sisa'),
                ];
            })
            ->values();

        // filter TAHUN (misalnya 2025)
        if ($request->filled('tahun')) {
            $rekapTahunan->where('bhb.tahun', $request->tahun);
        }

        // filter PERIODE (2 bulanan)
        if ($request->filled('periode')) {
            $p = (int) $request->periode;
            $bulanAwal = ($p - 1) * 2 + 1;
            $bulanAkhir = $bulanAwal + 1;

            $rekapTahunan
                ->whereBetween('bhb.bulan', [$bulanAwal, $bulanAkhir]);
        }


        $results = $results->transform(function ($row) use ($namaBulan) {
            $bulanAwal = (int) substr($row->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($row->bulan_akhir, 5, 2);
            $tahun = substr($row->bulan_awal, 0, 4);

            $row->periode = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";

            $nominal = $row->total_nominal ?? 0;
            $saldoLalu = $row->saldo_lalu ?? 0;
            $debit = $row->nominal_debit ?? 0;

            $row->total_hak = round($nominal + $saldoLalu, 2);
            $row->sisa = $debit > 0 ? 0 : $row->total_hak;

            $row->status_metode = $debit > 0
                ? $row->metode
                : 'Belum';

            return $row;
        });

        $hanyaFilterTahun =
            $request->filled('tahun')
            && !$request->filled('id_desa')
            && !$request->filled('id_tahun_tanam')
            && !$request->filled('periode')
            && !$request->filled('metode');


        $pdf = Pdf::loadView('saldo.pdf', [
            'dataSaldo' => $results,
            'stat' => $stat,
            'rekapTahunan' => $rekapTahunan,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
            'hanyaFilterTahun' => $hanyaFilterTahun,
            'request' => $request,
        ]);

        $pdf->setPaper('A4', $hanyaFilterTahun ? 'portrait' : 'landscape');

        //Mengambil nama desa
        $desa = $request->filled('id_desa')
            ? DB::table('desa')->where('id_desa', $request->id_desa)->value('desa')
            : 'Semua Desa';

        //Mengambil tahun tanam
        $tahunTanam = $request->filled('id_tahun_tanam')
            ? DB::table('tahun_tanam')->where('id_tahun_tanam', $request->id_tahun_tanam)->value('tahun')
            : 'TT';

        //Menentukan periode
        if ($request->filled('periode')) {
            $p = (int) $request->periode;
            $bulanAwal = ($p - 1) * 2 + 1; // contoh: periode 2 -> bulan 3
            $bulanAkhir = ($p - 1) * 2 + 2; // periode 2 -> bulan 4
            $periodeNama = "{$namaBulan[$bulanAwal]}-{$namaBulan[$bulanAkhir]}";
        } else {
            $periodeNama = 'Semua Periode';
        }

        // Tahun untuk file (ambil dari request atau default tahun sekarang)
        $tahunFile = $request->filled('tahun') ? $request->tahun : date('Y');

        // Buat nama file PDF
        $filename = "Laporan Saldo {$desa} {$tahunTanam} {$periodeNama} {$tahunFile}.pdf";

        // Download PDF
        return $pdf->download($filename);
    }
}
