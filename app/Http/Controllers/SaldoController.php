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
                DB::raw("(SELECT id_petani, MAX(id_bagi_petani) AS max_id 
                        FROM bagi_hasil_petani 
                        GROUP BY id_petani) bh2"),
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

        $subDebit = DB::table('transaksi')
            ->where('tipe', 'debit_pengambilan')
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',   // diasumsikan format 'YYYY-MM' seperti yang kamu tunjukkan
                'bulan_akhir',
                DB::raw('SUM(nominal) AS nominal_debit'),
                // ambil metode; jika ada beberapa metode dalam periode, MAX dipakai sebagai contoh.
                DB::raw('MAX(metode) AS metode')
            )
            ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'bulan_awal', 'bulan_akhir');

        // ------------------ QUERY UTAMA (join pake short fields) ------------------
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
            // left join sekarang berdasarkan bulan_awal_short / bulan_akhir_short -> cocok dengan transaksi 'YYYY-MM'
            ->leftJoinSub($subDebit, 'dpt', function ($join) {
                $join->on('s.id_petani', '=', 'dpt.id_petani')
                    ->on('s.id_desa', '=', 'dpt.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'dpt.id_tahun_tanam')
                    ->on('n.bulan_awal_short', '=', 'dpt.bulan_awal')
                    ->on('n.bulan_akhir_short', '=', 'dpt.bulan_akhir');
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

        $results = $query->orderBy('s.id_petani')
            ->paginate(15)
            ->appends($request->query());

        $stat = (clone $query)
            ->selectRaw("
        SUM(CASE WHEN dpt.nominal_debit IS NOT NULL THEN 1 ELSE 0 END) AS total_sudah,
        SUM(CASE WHEN dpt.nominal_debit IS NULL THEN 1 ELSE 0 END) AS total_belum,
        SUM(CASE WHEN dpt.metode = 'cash' THEN dpt.nominal_debit ELSE 0 END) AS total_cash,
        SUM(CASE WHEN dpt.metode = 'transfer' THEN dpt.nominal_debit ELSE 0 END) AS total_transfer
    ")
            ->groupBy(
                's.id_petani',
                's.nama_petani_snapshot',
                's.nomor_plasma_snapshot',
                'l.total_luas',
                'dd.desa',
                'tt.tahun',
                'n.bulan_awal',
                'n.bulan_akhir',
                'n.total_nominal',
                'dpt.metode',
                'dpt.nominal_debit'
            )
            ->first();

        $results->getCollection()->transform(function ($row) use ($namaBulan) {
            $bulanAwal = (int) substr($row->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($row->bulan_akhir, 5, 2);
            $tahun = substr($row->bulan_awal, 0, 4);

            $row->periode = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";

            $nominal = (float) $row->total_nominal;
            $debit = $row->nominal_debit !== null ? (float) $row->nominal_debit : 0.0;
            $sisa = max($nominal - $debit, 0);
            $row->sisa = $sisa;
            $row->status_metode = $debit > 0 ? $row->metode : "Belum diambil";

            return $row;
        });

        return view('saldo.index', [
            'dataSaldo' => $results,
            'stat' => $stat,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
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

        // ------------------ QUERY UTAMA ------------------
        $subSnapshot = DB::table('bagi_hasil_petani as bh1')
            ->join(DB::raw("(SELECT id_petani, MAX(id_bagi_petani) AS max_id 
                FROM bagi_hasil_petani GROUP BY id_petani) bh2"), function ($join) {
                $join->on('bh1.id_petani', '=', 'bh2.id_petani')
                    ->on('bh1.id_bagi_petani', '=', 'bh2.max_id');
            })
            ->select('bh1.id_petani', 'bh1.nama_petani_snapshot', 'bh1.nomor_plasma_snapshot', 'bh1.id_desa', 'bh1.id_tahun_tanam');

        $subLuas = DB::table('detail_kepemilikan as dk')
            ->join('kepemilikan as k', 'dk.id_kepemilikan', '=', 'k.id_kepemilikan')
            ->join('lahan as l', 'dk.id_lahan', '=', 'l.id_lahan')
            ->where('dk.status_pengelolaan', 'ksm')
            ->where('dk.status_kepemilikan', 'aktif')
            ->groupBy('k.id_petani', 'l.id_desa', 'l.id_tahun_tanam')
            ->select('k.id_petani', 'l.id_desa', 'l.id_tahun_tanam', DB::raw('ROUND(SUM(l.luas_peta)/10000,2) AS total_luas_ksm'));

        $subNominal = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->groupBy(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("bulan_awal"),
                DB::raw("bulan_akhir"),
                DB::raw("bulan_awal_short"),
                DB::raw("bulan_akhir_short")
            )
            ->select(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun,'-',LPAD(((FLOOR((bhb.bulan-1)/2)*2)+1),2,'0'),'-01') AS bulan_awal"),
                DB::raw("CONCAT(bhb.tahun,'-',LPAD(((FLOOR((bhb.bulan-1)/2)*2)+2),2,'0'),'-28') AS bulan_akhir"),
                DB::raw("CONCAT(bhb.tahun,'-',LPAD(((FLOOR((bhb.bulan-1)/2)*2)+1),2,'0')) AS bulan_awal_short"),
                DB::raw("CONCAT(bhb.tahun,'-',LPAD(((FLOOR((bhb.bulan-1)/2)*2)+2),2,'0')) AS bulan_akhir_short"),
                DB::raw('SUM(bhp.total_nominal) AS total_nominal')
            );

        $subDebit = DB::table('transaksi')
            ->where('tipe', 'debit_pengambilan')
            ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'bulan_awal', 'bulan_akhir')
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir',
                DB::raw('SUM(nominal) AS nominal_debit'),
                DB::raw('MAX(metode) AS metode')
            );

        $query = DB::table(DB::raw("({$subSnapshot->toSql()}) as s"))
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
            ->leftJoinSub($subDebit, 'dpt', function ($join) {
                $join->on('s.id_petani', '=', 'dpt.id_petani')
                    ->on('s.id_desa', '=', 'dpt.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'dpt.id_tahun_tanam')
                    ->on('n.bulan_awal_short', '=', 'dpt.bulan_awal')
                    ->on('n.bulan_akhir_short', '=', 'dpt.bulan_akhir');
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
                'dpt.metode',
                'dpt.nominal_debit'
            );

        // ---------------- FILTER -----------------
        if ($request->filled('id_desa'))
            $query->where('s.id_desa', $request->id_desa);
        if ($request->filled('id_tahun_tanam'))
            $query->where('s.id_tahun_tanam', $request->id_tahun_tanam);
        if ($request->filled('periode')) {
            $p = (int) $request->periode;
            $bAwal = str_pad(($p - 1) * 2 + 1, 2, '0', STR_PAD_LEFT);
            $bAkhir = str_pad(($p - 1) * 2 + 2, 2, '0', STR_PAD_LEFT);
            $query->where(DB::raw("SUBSTR(n.bulan_awal,6,2)"), $bAwal)
                ->where(DB::raw("SUBSTR(n.bulan_akhir,6,2)"), $bAkhir);
        }
        if ($request->filled('tahun'))
            $query->where(DB::raw("SUBSTR(n.bulan_awal,1,4)"), $request->tahun);
        if ($request->filled('metode')) {
            if ($request->metode === 'belum')
                $query->whereNull('dpt.nominal_debit');
            else
                $query->where('dpt.metode', $request->metode);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('s.nama_petani_snapshot', 'LIKE', "%$s%")
                    ->orWhere('s.nomor_plasma_snapshot', 'LIKE', "%$s%");
            });
        }

        $dataSaldo = $query->orderBy('s.id_petani')->get();

        // transform untuk PDF
        $dataSaldo->transform(function ($row) use ($namaBulan) {
            $bAwal = (int) substr($row->bulan_awal, 5, 2);
            $bAkhir = (int) substr($row->bulan_akhir, 5, 2);
            $row->periode = "{$namaBulan[$bAwal]} - {$namaBulan[$bAkhir]} " . substr($row->bulan_awal, 0, 4);
            $debit = $row->nominal_debit ?? 0;
            $row->sisa = max($row->total_nominal - $debit, 0);
            $row->status_metode = $debit > 0 ? $row->metode : "Belum diambil";
            return $row;
        });

        // --- HITUNG STAT CARD ---
        $totalPetani = $dataSaldo->count();
        $sudahMengambil = $dataSaldo->where('status_metode', '!=', 'Belum diambil')->count();
        $belumMengambil = $dataSaldo->where('status_metode', 'Belum diambil')->count();

        $cash = $dataSaldo->where('status_metode', 'cash');
        $transfer = $dataSaldo->where('status_metode', 'transfer');

        $jumlahCash = $cash->count();
        $nominalCash = $cash->sum('total_nominal');

        $jumlahTransfer = $transfer->count();
        $nominalTransfer = $transfer->sum('total_nominal');

        $stat = [
            'total_petani' => $totalPetani,
            'total_sudah' => $sudahMengambil,
            'total_belum' => $belumMengambil,
            'jumlah_cash' => $jumlahCash,
            'nominal_cash' => $nominalCash,
            'jumlah_transfer' => $jumlahTransfer,
            'nominal_transfer' => $nominalTransfer,
        ];

        $pdf = Pdf::loadView('saldo.pdf', [
            'dataSaldo' => $dataSaldo,
            'stat' => $stat,
            'namaBulan' => $namaBulan
        ]);

        $pdf->setPaper('A4', 'landscape');

        //Mengambil nama desa
        $desa = $request->filled('id_desa')
            ? DB::table('desa')->where('id_desa', $request->id_desa)->value('desa')
            : 'Tidak Diketahui';

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
            $periodeNama = 'Periode Tidak Diketahui';
        }

        // Tahun untuk file (ambil dari request atau default tahun sekarang)
        $tahunFile = $request->filled('tahun') ? $request->tahun : date('Y');

        // Buat nama file PDF
        $filename = "Laporan Saldo {$desa} {$tahunTanam} {$periodeNama} {$tahunFile}.pdf";

        // Download PDF
        return $pdf->download($filename);
    }
}
