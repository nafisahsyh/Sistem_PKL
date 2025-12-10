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
            'bulan_awal' => 'required|string',
        ]);

        // Tentukan periode 2 bulan
        $bulanAwalPeriode = strtotime($data['bulan_awal']);
        $bulanAkhirPeriode = strtotime("+1 month", $bulanAwalPeriode);
        $bulanAkhirPeriodeStr = date('Y-m', $bulanAkhirPeriode);

        DB::transaction(function () use ($data, $bulanAwalPeriode, $bulanAkhirPeriodeStr) {

            // Simpan data bulanan
            $bulan = BagiHasilBulanan::create($data);

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

                // Simpan bagi hasil petani
                BagiHasilPetani::create([
                    'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                    'id_petani' => $petani->id_petani,
                    'id_lahan' => $kepemilikan->id_lahan,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'total_luas_ksm' => $luasHa,
                    'total_nominal' => $nominalPetani,
                    'nama_petani_snapshot' => $petani->nama ?? null,
                    'nik_petani_snapshot' => $petani->NIK ?? null,
                    'alamat_petani_snapshot' => $petani->alamat ?? null,
                    'nomor_plasma_snapshot' => $petani->nomor_anggota_plasma ?? null,
                    'nomor_koperasi_snapshot' => $petani->nomor_anggota_koperasi ?? null,
                ]);

                // Ambil saldo terakhir
                $saldoTerakhir = Saldo::where('id_petani', $petani->id_petani)
                    ->where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->latest('created_at')
                    ->first();

                if (!$saldoTerakhir || strtotime($saldoTerakhir->bulan_akhir) < $bulanAwalPeriode - 1) {
                    // Buat saldo baru untuk periode 2 bulan
                    $saldo = Saldo::create([
                        'id_petani' => $petani->id_petani,
                        'id_desa' => $data['id_desa'],
                        'id_tahun_tanam' => $data['id_tahun_tanam'],
                        'bulan_awal' => $data['bulan_awal'],
                        'bulan_akhir' => $bulanAkhirPeriodeStr,
                        'saldo' => $nominalPetani,
                    ]);
                } else {
                    // Update saldo yang ada
                    $saldoTerakhir->saldo += $nominalPetani;
                    if (strtotime($data['bulan_awal']) > strtotime($saldoTerakhir->bulan_akhir)) {
                        $saldoTerakhir->bulan_akhir = $data['bulan_awal'];
                    }
                    $saldoTerakhir->save();
                    $saldo = $saldoTerakhir;
                }

                // Transaksi
                Transaksi::create([
                    'id_petani' => $petani->id_petani,
                    'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'tipe' => 'credit_bagihasil',
                    'metode' => null,
                    'nominal' => $nominalPetani,
                    'tanggal' => $data['tanggal_bagi'],
                    'bulan_awal' => $saldo->bulan_awal,
                    'bulan_akhir' => $saldo->bulan_akhir,
                    'keterangan' => 'Bagi hasil periode ' . $saldo->bulan_awal . ' - ' . $saldo->bulan_akhir,
                ]);
            }
        });

        return redirect()->route('bagi-hasil-bulanan.index')
            ->with('success', 'Data bulanan berhasil diproses.');
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

        // Pakai snapshot — ini nilai paling akurat
        $total_luas = $bulanan->luasan_total_snapshot;

        return view('bagihasil.edit', compact('bulanan', 'desa', 'tahunTanam', 'total_luas'));
    }

    public function update(Request $request, $id)
    {
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
            'bulan_awal' => 'required|string',
        ]);

        // Tentukan periode 2 bulan
        $periodeAwal = $data['bulan_awal'];
        $periodeAkhir = date('Y-m', strtotime($periodeAwal . ' +1 month'));

        DB::transaction(function () use ($data, $id, $periodeAwal, $periodeAkhir) {

            $bulan = BagiHasilBulanan::findOrFail($id);

            // Ambil snapshot lama SEBELUM dihapus
            $kelolaLama = BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->get();

            // Turunkan saldo lama sesuai periode 2 bulan
            foreach ($kelolaLama as $old) {
                $saldo = Saldo::where('id_petani', $old->id_petani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->where('bulan_awal', $bulan->bulan_awal)
                    ->where('bulan_akhir', $bulan->bulan_akhir)
                    ->first();

                if ($saldo) {
                    $saldo->saldo -= $old->total_nominal;
                    $saldo->save();
                }
            }

            // Hapus transaksi lama
            Transaksi::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->delete();

            // Hapus pembagian lama
            BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->delete();

            // Update header bulanan
            $bulan->update($data);

            $totalLuasHa = $bulan->luasan_total_snapshot;

            // Buat ulang pembagian & update saldo
            foreach ($kelolaLama as $old) {
                $nominalPetani = ($data['total_bagian'] / $totalLuasHa) * $old->total_luas_ksm;

                // Simpan kembali BagiHasilPetani
                BagiHasilPetani::create([
                    'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                    'id_petani' => $old->id_petani,
                    'id_lahan' => $old->id_lahan,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'total_luas_ksm' => $old->total_luas_ksm,
                    'total_nominal' => $nominalPetani,
                    'nama_petani_snapshot' => $old->nama_petani_snapshot,
                    'nik_petani_snapshot' => $old->nik_petani_snapshot,
                    'alamat_petani_snapshot' => $old->alamat_petani_snapshot,
                    'nomor_plasma_snapshot' => $old->nomor_plasma_snapshot,
                    'nomor_koperasi_snapshot' => $old->nomor_koperasi_snapshot,
                ]);

                // Update saldo (periode 2 bulan)
                $saldoTerakhir = Saldo::where('id_petani', $old->id_petani)
                    ->where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->latest('created_at')
                    ->first();

                if (!$saldoTerakhir || strtotime($periodeAwal) > strtotime($saldoTerakhir->bulan_akhir . ' +1 month')) {
                    // buat saldo baru
                    $saldo = Saldo::create([
                        'id_petani' => $old->id_petani,
                        'id_desa' => $data['id_desa'],
                        'id_tahun_tanam' => $data['id_tahun_tanam'],
                        'bulan_awal' => $periodeAwal,
                        'bulan_akhir' => $periodeAkhir,
                        'saldo' => $nominalPetani,
                    ]);
                } else {
                    // update saldo lama
                    $saldoTerakhir->saldo += $nominalPetani;
                    $saldoTerakhir->bulan_akhir = $periodeAkhir;
                    $saldoTerakhir->save();
                    $saldo = $saldoTerakhir;
                }

                // Simpan transaksi credit baru
                Transaksi::create([
                    'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                    'id_petani' => $old->id_petani,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'tipe' => 'credit_bagihasil',
                    'nominal' => $nominalPetani,
                    'tanggal' => $data['tanggal_bagi'],
                    'bulan_awal' => $saldo->bulan_awal,
                    'bulan_akhir' => $saldo->bulan_akhir,
                    'keterangan' => 'Bagi hasil periode ' . $saldo->bulan_awal . ' - ' . $saldo->bulan_akhir,
                ]);
            }
        });

        return redirect()->route('bagi-hasil-bulanan.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $bulan = BagiHasilBulanan::findOrFail($id);

            // Hapus distribusi petani & turunkan saldo
            $list = BagiHasilPetani::where('id_bagi_bulanan', $bulan->id)->get();
            foreach ($list as $d) {
                $saldo = Saldo::where('id_petani', $d->id_petani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->first();

                if ($saldo) {
                    $saldo->saldo -= $d->total_nominal;
                    $saldo->save();
                }

                Transaksi::where('id_petani', $d->id_petani)
                    ->where('tipe', 'credit_bagihasil')
                    ->where('tanggal', $bulan->tanggal_bagi)
                    ->delete();
            }

            BagiHasilPetani::where('id_bagi_bulanan', $bulan->id)->delete();

            // Hapus data bulan
            $bulan->delete();
        });

        return redirect()->route('bagi-hasil-bulanan.index')->with('success', 'Data berhasil dihapus.');
    }

    public function show($id)
    {
        $bulanan = BagiHasilBulanan::with(['desa', 'tahunTanam'])->findOrFail($id);

        // Ambil snapshot petani langsung dari bulan
        $petaniData = BagiHasilPetani::where('id_bagi_bulanan', $bulanan->id_bagi_bulanan)
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
            ->groupBy('id_petani')   // gabungkan per petani
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
            ->values();

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

        // Ambil data snapshot dari BagiHasilPetani langsung dari bulan
        $petaniData = BagiHasilPetani::where('id_bagi_bulanan', $bulanan->id_bagi_bulanan)
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
            ->groupBy('id_petani')  // gabungkan per petani
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
            ->values();

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

        return $pdf->download('Laporan Bagi Hasil.pdf');
    }
}
