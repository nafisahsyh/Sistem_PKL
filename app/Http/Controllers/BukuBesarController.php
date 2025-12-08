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
         * 1) Ambil total nominal per petani per PERIODE 2-bulanan
         *    Periode logic: periodeIndex = FLOOR((bulan-1)/2)
         *    bulan_awal = periodeIndex*2 + 1
         *    bulan_akhir = bulan_awal + 1
         */
        $subNominal = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',

                // Periode START dan END
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

            // FIX STRICT MODE → gunakan kolom hasil perhitungan untuk grouping
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
                DB::raw('ROUND(SUM(l.luas_peta) / 10000, 2) AS total_luas_ksm')
            )
            ->where('dk.status_pengelolaan', 'ksm')
            ->where('dk.status_kepemilikan', 'aktif')
            ->groupBy('k.id_petani', 'l.id_desa', 'l.id_tahun_tanam');

        /**
         * 3) Snapshot terakhir per petani (ambil row terakhir di bagi_hasil_petani)
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
         * 4) Gabungkan snapshot + luas + nominal per periode
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
            ->join('desa as d', 's.id_desa', '=', 'd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
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
            ->paginate(10);

        /**
         * 5) Format periode; pastikan total_nominal tetap numeric (float)
         *    Kita tidak mem-format number di sini — lakukan formatting di blade.
         */
        $query->getCollection()->transform(function ($trx) use ($namaBulan) {
            $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
            $tahun = substr($trx->bulan_akhir, 0, 4);

            $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";

            // cast total_nominal jadi float agar number_format di blade tidak error
            $trx->total_nominal = (float) $trx->total_nominal;

            return $trx;
        });

        return view('buku_besar.index', [
            'dataTransaksi' => $query,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
        ]);
    }
}
