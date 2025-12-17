<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        //===== FILTER AUTO ON =======//
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

        // Ambil snapshot terakhir per petani
        $subSnapshot = DB::table('bagi_hasil_petani as bh1')
            ->join(
                DB::raw('(
            SELECT id_petani, id_desa, id_tahun_tanam, MAX(id_bagi_petani) AS max_id
            FROM bagi_hasil_petani
            GROUP BY id_petani, id_desa, id_tahun_tanam
        ) bh2'),
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


        // Ambil id_bulanan sesuai filter periode (seperti di SHOW)
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

        // Ambil kredit (SUM per periode)
        $subKredit = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->select(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 1), 2, '0')) AS bulan_awal"),
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(((FLOOR((bhb.bulan - 1)/2) * 2) + 2), 2, '0')) AS bulan_akhir"),
                DB::raw('SUM(bhp.total_nominal) AS total_nominal')
            )
            ->groupBy(
                'bhp.id_petani',
                'bhb.id_desa',
                'bhb.id_tahun_tanam',
                'bulan_awal',
                'bulan_akhir'
            );


        // Jika PERIODE & TAHUN diisi
        if ($request->filled('periode') && $request->filled('tahun')) {
            $periode = (int) $request->periode;
            $tahun = $request->tahun;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $bulanAwal = $tahun . '-' . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhir = $tahun . '-' . str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);
        }

        // Jika PERIODE diisi tapi TAHUN tidak
        if ($request->filled('periode') && !$request->filled('tahun')) {
            $periode = (int) $request->periode;

            $bulanAwal = ($periode - 1) * 2 + 1;
            $bulanAkhir = $periode * 2;

            $bulanAwalStr = '-' . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT);
            $bulanAkhirStr = '-' . str_pad($bulanAkhir, 2, '0', STR_PAD_LEFT);

        }

        // Jika hanya TAHUN diisi
        if ($request->filled('tahun')) {
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
                    ->on('s.id_tahun_tanam', '=', 'k.id_tahun_tanam')
                    ->on('l.bulan_formatted', '=', 'k.bulan_awal');
            })
            ->join('desa as d', 's.id_desa', '=', 'd.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')
            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))
            ->select(
                's.id_petani',
                's.id_desa',
                's.id_tahun_tanam',
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

            // join subLuas dulu (mengandung bulan_formatted)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })

            // join subDebit (transaksi) — sambungkan juga periode transaksi ke subLuas
            ->joinSub($subDebit, 'd', function ($join) {
                $join->on('s.id_petani', '=', 'd.id_petani')
                    ->on('s.id_desa', '=', 'd.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'd.id_tahun_tanam');

                // paling penting — hubungkan bulan periode debit ke bulan_formatted di subLuas
                $join->on('l.bulan_formatted', '=', 'd.bulan_awal');
            })

            ->join('desa as desaTbl', 's.id_desa', '=', 'desaTbl.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')

            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))

            ->select(
                's.id_petani',
                's.id_desa',
                's.id_tahun_tanam',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',         // <-- sesuai periode transaksi!
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
        $dataTransaksi = $dataTransaksi
            ->sortByDesc(function ($row) {
                return $row->bulan_awal; // YYYY-MM → otomatis urut waktu
            })
            ->sortBy(function ($row) {
                return $row->id_petani;
            });

        $page = request()->get('page', 1);
        $perPage = 20;

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
        Carbon::setLocale('id');

        $id_petani = $request->id;
        $id_desa = $request->id_desa;
        $id_tahun_tanam = $request->id_tahun_tanam;
        $awal = $request->awal;   // yyyy-mm
        $akhir = $request->akhir; // yyyy-mm

        if (!$id_desa || !$id_tahun_tanam) {
            abort(404, 'Konteks buku besar tidak lengkap');
        }

        $periodeAwal = Carbon::parse($awal . '-01');
        $periodeAkhir = Carbon::parse($akhir . '-01');

        // Range bulan
        $bulanRange = [];
        $temp = $periodeAwal->copy();
        while ($temp <= $periodeAkhir) {
            $bulanRange[] = $temp->copy();
            $temp->addMonth();
        }

        $bagiHasil = DB::table('bagi_hasil_petani as bhp')
            ->join('bagi_hasil_bulanan as bhb', 'bhp.id_bagi_bulanan', '=', 'bhb.id_bagi_bulanan')
            ->where('bhp.id_petani', $id_petani)
            ->where('bhp.id_desa', $id_desa)
            ->where('bhp.id_tahun_tanam', $id_tahun_tanam)
            ->whereBetween(
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan,2,'0'))"),
                [$awal, $akhir]
            )
            ->select(
                'bhp.id_lahan',
                'bhp.id_desa',
                'bhp.id_tahun_tanam',
                'bhp.total_luas_ksm',
                'bhp.total_nominal',
                DB::raw("CONCAT(bhb.tahun, '-', LPAD(bhb.bulan,2,'0'), '-01') AS bulan_awal")
            )
            ->orderBy('bulan_awal')
            ->get();

        // 🔑 Group aman (snapshot identity)
        $groupLahan = $bagiHasil->groupBy(function ($item) {
            return implode('|', [
                $item->id_lahan,
                $item->id_desa,
                $item->id_tahun_tanam,
            ]);
        });

        $tabelData = [];
        $noTabel = 1;

        foreach ($bulanRange as $bulanObj) {
            $noLahan = 1;

            foreach ($groupLahan as $items) {
                $bh = $items->firstWhere(
                    'bulan_awal',
                    $bulanObj->format('Y-m-d')
                );

                if (!$bh) {
                    continue;
                }

                $tabelData[] = [
                    'no' => $noTabel,
                    'lahan' => $noLahan,
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
                    ->on('s.id_tahun_tanam', '=', 'k.id_tahun_tanam')
                    ->on('l.bulan_formatted', '=', 'k.bulan_awal');
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

            // join subLuas dulu (mengandung bulan_formatted)
            ->joinSub($subLuas, 'l', function ($join) {
                $join->on('s.id_petani', '=', 'l.id_petani')
                    ->on('s.id_desa', '=', 'l.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'l.id_tahun_tanam');
            })

            // join subDebit (transaksi) — sambungkan juga periode transaksi ke subLuas
            ->joinSub($subDebit, 'd', function ($join) {
                $join->on('s.id_petani', '=', 'd.id_petani')
                    ->on('s.id_desa', '=', 'd.id_desa')
                    ->on('s.id_tahun_tanam', '=', 'd.id_tahun_tanam');

                // paling penting — hubungkan bulan periode debit ke bulan_formatted di subLuas
                $join->on('l.bulan_formatted', '=', 'd.bulan_awal');
            })

            ->join('desa as desaTbl', 's.id_desa', '=', 'desaTbl.id_desa')
            ->join('tahun_tanam as tt', 's.id_tahun_tanam', '=', 'tt.id_tahun_tanam')

            ->when($request->filled('id_desa'), fn($q) => $q->where('s.id_desa', $request->id_desa))
            ->when($request->filled('id_tahun_tanam'), fn($q) => $q->where('s.id_tahun_tanam', $request->id_tahun_tanam))

            ->select(
                's.id_petani',
                's.nama_petani_snapshot AS nama_petani',
                's.nomor_plasma_snapshot AS nomor_plasma',
                'l.total_luas AS luasan',         // <-- sesuai periode transaksi!
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
