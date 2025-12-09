<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
                'l.total_luas_ksm AS luasan',
                'dd.desa AS nama_desa',
                'tt.tahun AS tahun_tanam',
                'n.bulan_awal',
                'n.bulan_akhir',
                'n.total_nominal',
                'dpt.metode',
                'dpt.nominal_debit'
            )
            ->orderBy('s.id_petani')
            ->paginate(20);

        $query->getCollection()->transform(function ($row) use ($namaBulan) {
            $bulanAwal = (int) substr($row->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($row->bulan_akhir, 5, 2);
            $tahun = substr($row->bulan_awal, 0, 4);

            $row->periode = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
            $nominal = (float) $row->total_nominal;
            $debit = $row->nominal_debit !== null ? (float) $row->nominal_debit : 0.0;
            $sisa = $nominal - $debit;
            if ($sisa < 0) $sisa = 0;
            $row->sisa = $sisa;
            $row->status_metode = $debit > 0 ? $row->metode : "Belum diambil";

            return $row;
        });

        return view('saldo.index', [
            'dataSaldo' => $query,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
        ]);
    }
}
