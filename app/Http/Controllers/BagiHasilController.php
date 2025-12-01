<?php

namespace App\Http\Controllers;

// Model yang terikat
use App\Models\Desa;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use App\Models\BagiHasilPetani;
use App\Models\BagiHasilBulanan;
use App\Models\BagiHasilPeriode;
use App\Models\DetailKepemilikan;

// Database dan Pagination
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use DateTime;

//Import PDF
use Barryvdh\DomPDF\Facade\Pdf;


class BagiHasilController extends Controller
{

    public function index(Request $request)
    {
        $query = BagiHasilBulanan::with(['desa', 'tahunTanam']);

        // FILTER DESA
        if ($request->filled('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        // FILTER TAHUN TANAM
        if ($request->filled('id_tahun_tanam')) {
            $query->where('id_tahun_tanam', $request->id_tahun_tanam);
        }

        // FILTER PERIODE (YYYY-MM)
        if ($request->filled('bulan_start')) {
            $query->whereRaw("
            CONCAT(tahun, '-', LPAD(bulan,2,'0')) >= ?
        ", [$request->bulan_start]);
        }

        if ($request->filled('bulan_end')) {
            $query->whereRaw("
            CONCAT(tahun, '-', LPAD(bulan,2,'0')) <= ?
        ", [$request->bulan_end]);
        }

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('desa', fn($d) => $d->where('desa', 'like', "%$search%"))
                    ->orWhereHas('tahunTanam', fn($t) => $t->where('tahun', 'like', "%$search%"));
            });
        }

        // ORDER
        $bulanan = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('bagihasil.index', [
            'bulanan' => $bulanan,
            'desa' => Desa::all(),
            'tahunTanam' => Tahun_Tanam::all(),
        ]);
    }


    public function create()
    {
        // Ambil semua desa dan tahun tanam untuk dropdown
        $desa = Desa::all();
        $tahunTanam = Tahun_Tanam::all();

        $totalLuasHa = 0;

        return view('bagihasil.create', [
            'desa' => $desa,
            'tahunTanam' => $tahunTanam,
            'totalLuasHa' => $totalLuasHa,
        ]);
    }

    // Input bagi hasil per bulan
    public function storeBulanan(Request $request)
    {
        // Konversi menghilangkan Rp, titik, spasi
        $request->merge([
            'total_bagian' => str_replace(['Rp', '.', ' '], '', $request->total_bagian)
        ]);

        $data = $request->validate([
            'id_desa' => 'required|integer',
            'id_tahun_tanam' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'tanggal_bagi' => 'required|date',
            'total_bagian' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($data) {

            // Simpan data bulanan
            $bulan = BagiHasilBulanan::create($data);

            // Cari bulan sebelumnya
            $prevMonth = $data['bulan'] - 1;
            $bulanSebelumnya = null;
            if ($prevMonth >= 1) {
                $bulanSebelumnya = BagiHasilBulanan::where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->where('bulan', $prevMonth)
                    ->where('tahun', $data['tahun'])
                    ->first();
            }

            // Total periode
            $totalPeriode = !empty($bulanSebelumnya) ? $bulanSebelumnya->total_bagian + $bulan->total_bagian : $bulan->total_bagian;

            // Buat periode
            $periode = BagiHasilPeriode::create([
                'id_desa' => $data['id_desa'],
                'id_tahun_tanam' => $data['id_tahun_tanam'],
                'bulan_awal' => $prevMonth,
                'bulan_akhir' => $data['bulan'],
                'tahun' => $data['tahun'],
                'tanggal_bagi' => $data['tanggal_bagi'],
                'total_periode' => $totalPeriode,
            ]);

            // Ambil petani aktif KSM saat ini
            $kelola = DetailKepemilikan::where('status_pengelolaan', 'ksm')
                ->where('status_kepemilikan', 'aktif')
                ->whereHas('kepemilikan.petani', fn($q) => $q->where('status', 'aktif'))
                ->whereHas(
                    'lahan',
                    fn($q) => $q
                        ->where('id_desa', $data['id_desa'])
                        ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                )
                ->with(['lahan', 'kepemilikan.petani'])
                ->get();

            // Hitung total luas lahan (Ha)
            $totalLuasHa = $kelola->sum(fn($item) => ($item->lahan->luas_peta ?? 0) / 10000);


            if ($totalLuasHa == 0) {
                throw new \Exception('Total luas lahan 0, bagi hasil tidak bisa diproses.');
            }
            $bulan->luasan_total_snapshot = $totalLuasHa;
            $bulan->save();

            foreach ($kelola as $kepemilikan) {
                $luasHa = ($kepemilikan->lahan->luas_peta ?? 0) / 10000;
                $nominalPetani = ($data['total_bagian'] / $totalLuasHa) * $luasHa;

                $petani = $kepemilikan->kepemilikan->petani;

                // Simpan BagiHasilPetani dengan snapshot
                BagiHasilPetani::create([
                    'id_bagi_periode' => $periode->id_bagi_periode,
                    'id_petani' => $petani->id_petani,
                    'total_luas_ksm' => $luasHa,
                    'total_nominal' => $nominalPetani,
                    // SNAPSHOT PETANI
                    'nama_petani_snapshot' => $petani->nama ?? null,
                    'nik_petani_snapshot' => $petani->NIK ?? null,
                    'alamat_petani_snapshot' => $petani->alamat ?? null,
                    'nomor_plasma_snapshot' => $petani->nomor_anggota_plasma ?? null,
                    'nomor_koperasi_snapshot' => $petani->nomor_anggota_koperasi ?? null,
                ]);

                // Update saldo
                $saldo = Saldo::firstOrCreate([
                    'id_petani' => $petani->id_petani,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                ]);

                $saldo->saldo += $nominalPetani;
                $saldo->save();

                // Buat transaksi
                Transaksi::create([
                    'id_petani' => $petani->id_petani,
                    'tipe' => 'credit_bagihasil',
                    'metode' => null,
                    'nominal' => $nominalPetani,
                    'tanggal' => $data['tanggal_bagi'],
                    'keterangan' => 'Bagi hasil periode otomatis',
                ]);
            }
        });

        return redirect()->route('bagi-hasil-bulanan.index')
            ->with('success', 'Data bulanan dan periode berhasil diproses.');
    }


    public function getTotalLuas(Request $request)
    {
        $idDesa = $request->query('id_desa');
        $idTahun = $request->query('id_tahun_tanam');

        if (!$idDesa || !$idTahun) {
            return response()->json(['total_luas' => 0]);
        }

        $kelola = DetailKepemilikan::where('status_pengelolaan', 'ksm')
            ->where('status_kepemilikan', 'aktif')
            ->whereHas(
                'lahan',
                fn($q) => $q
                    ->where('id_desa', $idDesa)
                    ->where('id_tahun_tanam', $idTahun)
            )
            ->with('lahan') // ambil relasi lahan
            ->get();

        // Hitung total luas dari relasi lahan dan convert ke Ha
        $totalLuasHa = $kelola->sum(fn($item) => $item->lahan->luas_peta ?? 0) / 10000;

        return response()->json(['total_luas' => round($totalLuasHa, 2)]); // 2 desimal
    }

    // EDIT
    public function edit($id)
    {
        $bulanan = BagiHasilBulanan::findOrFail($id);
        $desa = Desa::all();
        $tahunTanam = Tahun_Tanam::all();


        $total_luas = DetailKepemilikan::whereHas('lahan', function ($q) use ($bulanan) {
            $q->where('id_desa', $bulanan->id_desa)
                ->where('id_tahun_tanam', $bulanan->id_tahun_tanam);
        })
            ->with('lahan')
            ->get()
            ->sum(fn($item) => $item->lahan->luas_peta ?? 0);

        $total_luas /= 10000; // convert ke Ha

        return view('bagihasil.edit', compact('bulanan', 'desa', 'tahunTanam', 'total_luas'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        //Pembersihan titik koma
        $request->merge([
            'total_bagian' => preg_replace('/[^0-9]/', '', $request->total_bagian)
        ]);

        $data = $request->validate([
            'id_desa' => 'required|integer',
            'id_tahun_tanam' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'tanggal_bagi' => 'required|date',
            'total_bagian' => 'required|numeric|min:1',
        ]);


        DB::transaction(function () use ($data, $id) {

            $bulan = BagiHasilBulanan::findOrFail($id);
            $bulan->update($data);

            $periode = BagiHasilPeriode::firstOrCreate(
                [
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'bulan_akhir' => $data['bulan'],
                    'tahun' => $data['tahun'],
                ],
                [
                    'bulan_awal' => $data['bulan'], // wajib
                    'total_periode' => 0,
                    'tanggal_bagi' => $data['tanggal_bagi'],
                ]
            );

            if ($periode) {
                // Update total periode
                $periode->total_periode = $data['total_bagian'];
                $periode->tanggal_bagi = $data['tanggal_bagi'];
                $periode->save();

                // Hapus distribusi sebelumnya
                $list = BagiHasilPetani::where('id_bagi_periode', $periode->id_bagi_periode)->get();
                foreach ($list as $d) {
                    $saldo = Saldo::where('id_petani', $d->id_petani)
                        ->where('id_desa', $data['id_desa'])
                        ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                        ->first();

                    if ($saldo) {
                        $saldo->saldo -= $d->total_nominal;
                        $saldo->save();
                    }

                    Transaksi::where('id_petani', $d->id_petani)
                        ->where('tipe', 'credit_bagihasil')
                        ->where('tanggal', $periode->tanggal_bagi)
                        ->delete();
                }

                BagiHasilPetani::where('id_bagi_periode', $periode->id_bagi_periode)->delete();
            }

            // Ambil petani aktif kembali melalui relasi lahan
            $kelola = DetailKepemilikan::where('status_pengelolaan', 'ksm')
                ->where('status_kepemilikan', 'aktif')
                ->whereHas('kepemilikan.petani', fn($q) => $q->where('status', 'aktif'))
                ->whereHas(
                    'lahan',
                    fn($q) =>
                    $q->where('id_desa', $data['id_desa'])
                        ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                )
                ->with(['lahan', 'kepemilikan.petani'])
                ->get();

            $totalLuasHa = $kelola->sum(fn($item) => ($item->lahan->luas_peta ?? 0) / 10000);

            foreach ($kelola as $kepemilikan) {
                $luasHa = ($kepemilikan->lahan->luas_peta ?? 0) / 10000;
                $nominal = ($data['total_bagian'] / $totalLuasHa) * $luasHa;

                $petaniId = $kepemilikan->kepemilikan->id_petani;

                BagiHasilPetani::create([
                    'id_bagi_periode' => $periode->id_bagi_periode,
                    'id_petani' => $petaniId,
                    'total_luas_ksm' => $luasHa,
                    'total_nominal' => $nominal,
                ]);

                $saldo = Saldo::firstOrCreate([
                    'id_petani' => $petaniId,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                ]);

                $saldo->saldo += $nominal;
                $saldo->save();

                Transaksi::create([
                    'id_petani' => $petaniId,
                    'tipe' => 'credit_bagihasil',
                    'metode' => null,
                    'nominal' => $nominal,
                    'tanggal' => $data['tanggal_bagi'],
                    'keterangan' => 'Update bagi hasil',
                ]);
            }
        });

        return redirect()->route('bagi-hasil-bulanan.index')->with('success', 'Data berhasil diupdate.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $bulan = BagiHasilBulanan::findOrFail($id);

            $periode = BagiHasilPeriode::where('id_desa', $bulan->id_desa)
                ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                ->where('bulan_akhir', $bulan->bulan)
                ->where('tahun', $bulan->tahun)
                ->first();

            if ($periode) {
                $list = BagiHasilPetani::where('id_bagi_periode', $periode->id_bagi_periode)->get();

                foreach ($list as $d) {

                    // turunkan saldo
                    $saldo = Saldo::where('id_petani', $d->id_petani)
                        ->where('id_desa', $bulan->id_desa)
                        ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                        ->first();

                    if ($saldo) {
                        $saldo->saldo -= $d->total_nominal;
                        $saldo->save();
                    }

                    // hapus transaksi
                    Transaksi::where('id_petani', $d->id_petani)
                        ->where('tipe', 'credit_bagihasil')
                        ->where('tanggal', $periode->tanggal_bagi)
                        ->delete();
                }

                BagiHasilPetani::where('id_bagi_periode', $periode->id_bagi_periode)->delete();
                $periode->delete();
            }

            $bulan->delete();
        });

        return redirect()->route('bagi-hasil-bulanan.index')->with('success', 'Data berhasil dihapus.');
    }

    public function show($id)
    {
        $bulanan = BagiHasilBulanan::with(['desa', 'tahunTanam'])->findOrFail($id);

        // Cari periode yang sesuai
        $periode = BagiHasilPeriode::where('id_desa', $bulanan->id_desa)
            ->where('id_tahun_tanam', $bulanan->id_tahun_tanam)
            ->where('bulan_akhir', $bulanan->bulan)
            ->where('tahun', $bulanan->tahun)
            ->first();

        // Ambil snapshot petani dan akumulasi per petani
        $petaniData = $periode
            ? BagiHasilPetani::where('id_bagi_periode', $periode->id_bagi_periode)
                ->select(
                    'id_petani',
                    'nama_petani_snapshot as nama_petani',
                    'nik_petani_snapshot as nik_petani',
                    'nomor_plasma_snapshot as no_plasma',
                    'nomor_koperasi_snapshot as no_koperasi',
                    'total_luas_ksm as luas_ha',
                    'total_nominal as nominal'
                )
                ->get()
                ->groupBy('id_petani')   // ← FIX TERPENTING
                ->map(function ($group) {
                    return [
                        'id_petani' => $group->first()->id_petani,
                        'nama_petani' => $group->first()->nama_petani,
                        'nik_petani' => $group->first()->nik_petani,
                        'no_plasma' => $group->first()->no_plasma,
                        'no_koperasi' => $group->first()->no_koperasi,
                        'luas_ha' => $group->sum('luas_ha'),
                        'nominal' => $group->sum('nominal'),
                    ];
                })
                ->values()
            : collect();


        // Filter search jika ada
        $search = request('search');
        if ($search) {
            $petaniData = $petaniData->filter(function ($p) use ($search) {
                return str_contains(strtolower($p['no_plasma'] ?? ''), strtolower($search))
                    || str_contains(strtolower($p['no_koperasi'] ?? ''), strtolower($search))
                    || str_contains(strtolower($p['nama_petani'] ?? ''), strtolower($search));
            })->values();
        }

        // Pagination manual
        $perPage = 10;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = new LengthAwarePaginator(
            $petaniData->slice($offset, $perPage)->values(),
            $petaniData->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        $noStart = ($page - 1) * $perPage + 1;

        // Total luas lahan dari snapshot
        $totalLuasHa = $petaniData->sum('luas_ha');

        return view('bagihasil.detail', [
            'bulanan' => $bulanan,
            'petaniData' => $paginated,
            'totalLuasHa' => $totalLuasHa,
            'noStart' => $noStart
        ]);
    }

    //Mengambil data petani untuk PDF dari Show()
    public function detailPdf($id)
    {
        $bulanan = BagiHasilBulanan::with(['desa', 'tahunTanam'])->findOrFail($id);

        // Cari periode yang sesuai (sama seperti show)
        $periode = BagiHasilPeriode::where('id_desa', $bulanan->id_desa)
            ->where('id_tahun_tanam', $bulanan->id_tahun_tanam)
            ->where('bulan_akhir', $bulanan->bulan)
            ->where('tahun', $bulanan->tahun)
            ->first();

        // Ambil data snapshot dari BagiHasilPetani
        $petaniData = $periode
            ? BagiHasilPetani::where('id_bagi_periode', $periode->id_bagi_periode)
                ->select(
                    'id_petani',
                    'nama_petani_snapshot as nama_petani',
                    'nik_petani_snapshot as nik_petani',
                    'nomor_plasma_snapshot as no_plasma',
                    'nomor_koperasi_snapshot as no_koperasi',
                    'total_luas_ksm as luas_ha',
                    'total_nominal as nominal'
                )
                ->get()
                ->groupBy('id_petani')
                ->map(function ($group) {
                    return [
                        'id_petani' => $group->first()->id_petani,
                        'nama_petani' => $group->first()->nama_petani,
                        'nik_petani' => $group->first()->nik_petani,
                        'no_plasma' => $group->first()->no_plasma,
                        'no_koperasi' => $group->first()->no_koperasi,
                        'luas_ha' => $group->sum('luas_ha'),
                        'nominal' => $group->sum('nominal'),
                    ];
                })
                ->values()
            : collect();

        // Total luas snapshot
        $totalLuasHa = $petaniData->sum('luas_ha');

        // Generate PDF
        $pdf = Pdf::loadView('bagihasil.pdf_detail', [
            'bulanan' => $bulanan,
            'petaniData' => $petaniData,
            'totalLuasHa' => $totalLuasHa,
        ]);

        $namaFile =
            'Bagi Hasil - ' .
            $bulanan->desa->desa . ' - ' .
            $bulanan->tahunTanam->tahun . ' - ' .
            DateTime::createFromFormat('!m', $bulanan->bulan)->format('F') . ' ' .
            $bulanan->tahun . '.pdf';

        return $pdf->download($namaFile);
    }

    public function cetakPdf(Request $request)
    {
        $query = BagiHasilBulanan::with(['desa', 'tahunTanam']);

        // Filter Desa
        if ($request->filled('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        // Filter Tahun Tanam
        if ($request->filled('id_tahun_tanam')) {
            $query->where('id_tahun_tanam', $request->id_tahun_tanam);
        }

        // Filter Periode - Bulan & Tahun
        if ($request->filled('bulan_start')) {
            [$startYear, $startMonth] = explode('-', $request->bulan_start);
            $query->where(function ($q) use ($startYear, $startMonth) {
                $q->where('tahun', '>', $startYear)
                    ->orWhere(function ($q2) use ($startYear, $startMonth) {
                        $q2->where('tahun', $startYear)
                            ->where('bulan', '>=', $startMonth);
                    });
            });
        }

        if ($request->filled('bulan_end')) {
            [$endYear, $endMonth] = explode('-', $request->bulan_end);
            $query->where(function ($q) use ($endYear, $endMonth) {
                $q->where('tahun', '<', $endYear)
                    ->orWhere(function ($q2) use ($endYear, $endMonth) {
                        $q2->where('tahun', $endYear)
                            ->where('bulan', '<=', $endMonth);
                    });
            });
        }

        // Ambil data akhir
        $bulanan = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $pdf = PDF::loadView('bagihasil.pdf_index', compact('bulanan'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-bagi-hasil.pdf');
    }
}
