<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petani;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\Transaksi;
use App\Models\BagiHasilPetani;
use App\Models\BagiHasilBulanan;
use DB;

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

        // 1️⃣ SUM nominal per petani per periode (hindari dobel)
        $subTransaksi = Transaksi::query()
            ->when($request->filled('id_desa'), fn($q) => $q->where('id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('id_tahun_tanam', $request->id_tahun_tanam))
            ->when($request->filled('tipe'), fn($q) => $q->where('tipe', $request->tipe))
            ->select(
                'id_petani',
                'id_desa',
                'id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir',
                \DB::raw('SUM(nominal) as total_nominal')
            )
            ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam', 'bulan_awal', 'bulan_akhir');

        // 2️⃣ Ambil total luas unik per petani
        $subLuas = \DB::table('bagi_hasil_petani')
            ->select('id_petani', 'id_desa', 'id_tahun_tanam', \DB::raw('SUM(total_luas_ksm) as total_luas_ksm'))
            ->groupBy('id_petani', 'id_desa', 'id_tahun_tanam');

        // 3️⃣ Ambil snapshot terbaru per petani
        $subSnapshot = \DB::table('bagi_hasil_petani as bh1')
            ->join(
                \DB::raw('(SELECT id_petani, MAX(id_bagi_petani) AS max_id FROM bagi_hasil_petani GROUP BY id_petani) bh2'),
                fn($join) => $join->on('bh1.id_petani', '=', 'bh2.id_petani')
                    ->on('bh1.id_bagi_petani', '=', 'bh2.max_id')
            )
            ->select('bh1.id_petani', 'bh1.nama_petani_snapshot', 'bh1.nomor_plasma_snapshot', 'bh1.id_desa', 'bh1.id_tahun_tanam');

        // 4️⃣ Gabungkan snapshot + luas + transaksi per periode
        $query = \DB::table(\DB::raw("({$subSnapshot->toSql()}) as s"))
            ->mergeBindings($subSnapshot)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })
            ->joinSub($subTransaksi, 't', function ($join) {
                $join->on('s.id_petani', '=', 't.id_petani')
                    ->on('s.id_desa', '=', 't.id_desa')
                    ->on('s.id_tahun_tanam', '=', 't.id_tahun_tanam');
            })
            ->join('desa as d', 's.id_desa', '=', 'd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->select(
                's.id_petani',
                's.nama_petani_snapshot as nama_petani',
                's.nomor_plasma_snapshot as nomor_plasma',
                'l.total_luas_ksm as luasan',
                'd.desa as nama_desa',
                'tt.tahun as tahun_tanam',
                't.bulan_awal',
                't.bulan_akhir',
                't.total_nominal'
            )
            ->orderBy('s.id_petani')
            ->paginate(10);

        // 🔥 Format periode
        $query->getCollection()->transform(function ($trx) use ($namaBulan) {
            $bulanAwal = (int) substr($trx->bulan_awal, 5, 2);
            $bulanAkhir = (int) substr($trx->bulan_akhir, 5, 2);
            $tahun = substr($trx->bulan_akhir, 0, 4);
            $trx->periode_string = "{$namaBulan[$bulanAwal]} - {$namaBulan[$bulanAkhir]} {$tahun}";
            return $trx;
        });

        return view('buku_besar.index', [
            'dataTransaksi' => $query,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
        ]);
    }

    public function show($id_petani, $bulan_awal, $bulan_akhir)
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

        // Ambil data petani
        $petani = Petani::with('desa', 'tahunTanam')->findOrFail($id_petani);

        // Ambil transaksi per bulan & per lahan
        $transaksi = BagiHasilPetani::with('bagiHasilBulanan')
            ->where('id_petani', $id_petani)
            ->whereHas('bagiHasilBulanan', function ($q) use ($bulan_awal, $bulan_akhir) {
                $q->whereBetween('bulan', [
                    (int) substr($bulan_awal, 5, 2),
                    (int) substr($bulan_akhir, 5, 2)
                ])
                    ->whereBetween('tahun', [
                        (int) substr($bulan_awal, 0, 4),
                        (int) substr($bulan_akhir, 0, 4)
                    ]);
            })
            ->get();

        // Format bulan
        foreach ($transaksi as $trx) {
            foreach ($trx->bagiHasilBulanan as $bh) {
                $bh->bulan_string = $namaBulan[$bh->bulan];
            }
        }

        return view('buku_besar.show', compact('petani', 'transaksi'));
    }
}
