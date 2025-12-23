<?php

namespace App\Http\Controllers;

// Model yang terikat
use DateTime;
use Carbon\Carbon;
use App\Models\Desa;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use App\Models\BagiHasilPetani;
use App\Models\SaldoLalu;

// Database dan Pagination
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BagiHasilBulanan;
use App\Models\DetailKepemilikan;
use Illuminate\Support\Facades\DB;

//Import PDF
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;


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

        $bulanan->getCollection()->transform(function ($b) {

            $bulanIni = sprintf('%04d-%02d', $b->tahun, $b->bulan);

            $b->sudah_diambil = Transaksi::where('tipe', 'debit_pengambilan')
                ->where('id_desa', $b->id_desa)
                ->where('id_tahun_tanam', $b->id_tahun_tanam)

                // 🔑 BULAN INI ADA DI DALAM PERIODE TRANSAKSI
                ->where('bulan_awal', '<=', $bulanIni)
                ->where('bulan_akhir', '>=', $bulanIni)

                ->exists();

            return $b;
        });


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

            $bulanAwalValid = in_array((int) $data['bulan'], [1, 3, 5, 7, 9, 11]);

            $sisaSaldo = 0;

            if ($bulanAwalValid) {
                $sisaSaldo = Saldo::where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->where('bulan_akhir', '<', $data['bulan_awal'])
                    ->where('saldo', '>', 0)
                    ->sum('saldo');
            }

            $bulan->luasan_total_snapshot = $totalLuasHa;
            $bulan->sisa_saldo_snapshot = $sisaSaldo;
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
                    'id_desa' => $kepemilikan->lahan->id_desa,
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'total_luas_ksm' => $luasHa,
                    'total_nominal' => $nominalPetani,
                    'id_petani_snapshot' => $petani->id_petani ?? null,
                    'id_desa_snapshot' => $kepemilikan->lahan->id_desa,
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

                $saldoAwalPeriode = $saldoTerakhir?->saldo ?? 0;

                if ($bulanAwalValid) {
                    SaldoLalu::firstOrCreate(
                        [
                            'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                            'id_petani' => $petani->id_petani,
                        ],
                        [
                            'id_desa' => $data['id_desa'],
                            'id_tahun_tanam' => $data['id_tahun_tanam'],
                            'saldo_lalu' => $saldoAwalPeriode,
                        ]
                    );
                }

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

    public function getSisaSaldo(Request $request)
    {
        if (
            !$request->id_desa ||
            !$request->id_tahun_tanam ||
            !$request->bulan_awal
        ) {
            return response()->json(['sisa_saldo' => 0]);
        }

        $sisaSaldo = Saldo::where('id_desa', $request->id_desa)
            ->where('id_tahun_tanam', $request->id_tahun_tanam)
            ->where('bulan_akhir', '<', $request->bulan_awal)
            ->where('saldo', '>', 0)
            ->sum('saldo');

        return response()->json([
            'sisa_saldo' => $sisaSaldo
        ]);
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
        ]);

        try {
            DB::transaction(function () use ($data, $id) {

                /** Ambil header Bagi Hasil Bulanan */
                $bulan = BagiHasilBulanan::lockForUpdate()->findOrFail($id);

                $bulanYangDiupdate = $data['tahun'] . '-' . str_pad($data['bulan'], 2, '0', STR_PAD_LEFT);

                /** Ambil data Bagi Hasil Petani lama untuk bulan ini */
                $listLama = BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                    ->get();

                if ($listLama->isEmpty()) {
                    throw new \Exception('Data pembagian lama kosong');
                }

                /** Rollback saldo hanya untuk bulan ini */
                foreach ($listLama as $old) {
                    $saldo = Saldo::where('id_petani', $old->id_petani)
                        ->where('id_desa', $bulan->id_desa)
                        ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                        ->where('bulan_awal', '<=', $bulanYangDiupdate)
                        ->where('bulan_akhir', '>=', $bulanYangDiupdate)
                        ->lockForUpdate()
                        ->first();

                    if ($saldo) {
                        // Hapus hanya bagian bulan ini
                        $saldo->saldo -= $old->total_nominal;
                        if ($saldo->saldo < 0) {
                            throw new \Exception('Rollback saldo gagal');
                        }
                        $saldo->save();
                    }
                }

                /** Hapus transaksi bulan ini */
                Transaksi::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                    ->where('bulan_awal', '<=', $bulanYangDiupdate)
                    ->where('bulan_akhir', '>=', $bulanYangDiupdate)
                    ->delete();

                /** Hapus data Bagi Hasil Petani bulan ini */
                BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                    ->delete();

                /** Update header Bagi Hasil Bulanan */
                $bulan->update($data);

                /** Hitung distribusi baru */
                $totalLuas = $bulan->luasan_total_snapshot;
                if ($totalLuas <= 0) {
                    throw new \Exception('Total luas lahan tidak valid');
                }

                foreach ($listLama as $old) {
                    $nominalBaru = ($data['total_bagian'] / $totalLuas) * $old->total_luas_ksm;

                    // Simpan data baru Bagi Hasil Petani
                    BagiHasilPetani::create([
                        'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                        'id_petani' => $old->id_petani,
                        'id_lahan' => $old->id_lahan,
                        'id_desa' => $old->id_desa,
                        'id_tahun_tanam' => $old->id_tahun_tanam,
                        'total_luas_ksm' => $old->total_luas_ksm,
                        'total_nominal' => $nominalBaru,
                        'id_petani_snapshot' => $old->id_petani_snapshot,
                        'id_desa_snapshot' => $old->id_desa_snapshot,
                        'nama_petani_snapshot' => $old->nama_petani_snapshot,
                        'nik_petani_snapshot' => $old->nik_petani_snapshot,
                        'alamat_petani_snapshot' => $old->alamat_petani_snapshot,
                        'nomor_plasma_snapshot' => $old->nomor_plasma_snapshot,
                        'nomor_koperasi_snapshot' => $old->nomor_koperasi_snapshot,
                    ]);

                    // Update atau buat saldo bulan ini
                    $saldo = Saldo::where('id_petani', $old->id_petani)
                        ->where('id_desa', $bulan->id_desa)
                        ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                        ->where('bulan_awal', '<=', $bulanYangDiupdate)
                        ->where('bulan_akhir', '>=', $bulanYangDiupdate)
                        ->lockForUpdate()
                        ->first();

                    if (!$saldo) {
                        $saldo = Saldo::create([
                            'id_petani' => $old->id_petani,
                            'id_desa' => $bulan->id_desa,
                            'id_tahun_tanam' => $bulan->id_tahun_tanam,
                            'bulan_awal' => $bulanYangDiupdate,
                            'bulan_akhir' => $bulanYangDiupdate,
                            'saldo' => $nominalBaru,
                        ]);
                    } else {
                        $saldo->saldo += $nominalBaru;
                        $saldo->save();
                    }

                    // Buat transaksi baru bulan ini
                    Transaksi::create([
                        'id_petani' => $old->id_petani,
                        'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                        'id_desa' => $data['id_desa'],
                        'id_tahun_tanam' => $data['id_tahun_tanam'],
                        'tipe' => 'credit_bagihasil',
                        'nominal' => $nominalBaru,
                        'tanggal' => $data['tanggal_bagi'],
                        'bulan_awal' => $bulanYangDiupdate,
                        'bulan_akhir' => $bulanYangDiupdate,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('bagi-hasil-bulanan.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $bulan = BagiHasilBulanan::findOrFail($id);

            // Ambil semua pembagian petani bulan ini
            $list = BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->get();

            foreach ($list as $d) {

                // Ambil transaksi bulan ini
                $trx = Transaksi::where('id_petani', $d->id_petani)
                    ->where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                    ->where('tipe', 'credit_bagihasil')
                    ->first();

                if (!$trx) {
                    continue; // jika tidak ada transaksi, lewati
                }

                // Ambil saldo yang meng-cover periode transaksi ini
                $saldo = Saldo::where('id_petani', $d->id_petani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->where('bulan_awal', '<=', $trx->bulan_awal)
                    ->where('bulan_akhir', '>=', $trx->bulan_akhir)
                    ->lockForUpdate()
                    ->first();

                if ($saldo) {
                    // Kurangi saldo hanya dari nominal bulan ini
                    $saldo->saldo -= $d->total_nominal;

                    if ($saldo->saldo < 0) {
                        throw new \Exception(
                            'Saldo petani ID ' . $d->id_petani . ' menjadi negatif.'
                        );
                    }

                    $saldo->save();
                }
            }

            // Hapus transaksi bulan ini saja
            Transaksi::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                ->where('tipe', 'credit_bagihasil')
                ->delete();

            // Hapus detail bagi hasil petani bulan ini
            BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->delete();

            // Hapus header bulanan
            $bulan->delete();
        });

        return redirect()
            ->route('bagi-hasil-bulanan.index')
            ->with('success', 'Bagi hasil bulan ini berhasil dihapus dan saldo diperbarui.');
    }

    public function show($id)
    {
        $bulanan = BagiHasilBulanan::with(['desa', 'tahunTanam'])->findOrFail($id);
        $bulanAwalSekarang = sprintf('%04d-%02d', $bulanan->tahun, $bulanan->bulan);
        $isBulanAwal = $bulanan->bulan % 2 === 1;

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
            ->map(function ($group) use ($bulanan) {
                $idPetani = $group->first()->id_petani;

                // Nominal bulan berjalan
                $nominalBulanIni = $group->sum('nominal');

                // Ambil Snapshoot lalu
                $sisaSaldo = SaldoLalu::where('id_bagi_bulanan', $bulanan->id_bagi_bulanan)
                    ->where('id_petani', $idPetani)
                    ->value('saldo_lalu') ?? 0;

                return [
                    'id_petani' => $idPetani,
                    'nama_petani' => $group->first()->nama_petani,
                    'nik_petani' => $group->first()->nik_petani,
                    'no_plasma' => $group->first()->no_plasma,
                    'no_koperasi' => $group->first()->no_koperasi,
                    'luas_ha' => $group->sum('luas_ha'),

                    // nilai tampilan
                    'nominal_bulan_ini' => $nominalBulanIni,
                    'sisa_saldo' => $sisaSaldo,
                    'total_hak' => $nominalBulanIni + $sisaSaldo,
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

        $bulanAwalSekarang = sprintf('%04d-%02d', $bulanan->tahun, $bulanan->bulan);
        $isBulanAwal = $bulanan->bulan % 2 === 1;

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
            ->map(function ($group) use ($bulanan, $bulanAwalSekarang, $isBulanAwal) {
                $idPetani = $group->first()->id_petani;

                // Nominal bulan berjalan
                $nominalBulanIni = $group->sum('nominal');

                // Default
                $sisaSaldo = 0;
                $totalHak = $nominalBulanIni;

                // 🔥 HANYA JIKA BULAN AWAL PERIODE
                if ($isBulanAwal) {
                    $sisaSaldo = Saldo::where('id_petani', $idPetani)
                        ->where('id_desa', $bulanan->id_desa)
                        ->where('id_tahun_tanam', $bulanan->id_tahun_tanam)
                        ->where('bulan_akhir', '<', $bulanAwalSekarang)
                        ->where('saldo', '>', 0)
                        ->sum('saldo');

                    $totalHak = $nominalBulanIni + $sisaSaldo;
                }

                return [
                    'id_petani' => $idPetani,
                    'nama_petani' => $group->first()->nama_petani,
                    'nik_petani' => $group->first()->nik_petani,
                    'no_plasma' => $group->first()->no_plasma,
                    'no_koperasi' => $group->first()->no_koperasi,
                    'luas_ha' => $group->sum('luas_ha'),

                    // 👇 nilai tampilan
                    'nominal_bulan_ini' => $nominalBulanIni,
                    'sisa_saldo' => $sisaSaldo,        // 0 kalau bulan akhir
                    'total_hak' => $totalHak,           // = nominal_bulan_ini kalau bulan akhir
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
