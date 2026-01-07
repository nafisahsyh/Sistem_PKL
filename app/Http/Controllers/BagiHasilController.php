<?php

namespace App\Http\Controllers;

// Model yang terikat
use DateTime;
use Carbon\Carbon;
use App\Models\Desa;
use App\Models\Saldo;
use App\Models\SaldoLalu;
use App\Models\Transaksi;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use App\Models\BagiHasilPetani;
use Illuminate\Validation\Rule;

// Database dan Pagination
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BagiHasilBulanan;
use App\Models\DetailKepemilikan;
use Illuminate\Support\Facades\DB;

//Import PDF
use Illuminate\Pagination\Paginator;
use Illuminate\Database\QueryException;
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
            'bulan' => [
                'required',
                'integer',
                Rule::unique('bagi_hasil_bulanan')
                    ->where('id_desa', $request->id_desa)
                    ->where('id_tahun_tanam', $request->id_tahun_tanam)
                    ->where('tahun', $request->tahun),
            ],

            'tahun' => 'required|integer',
            'tanggal_bagi' => 'required|date',
            'total_bagian' => 'required|numeric',
            'bulan_awal' => 'required|string',
        ]);

        // Tentukan periode 2 bulan
        $bulanAwalPeriode = strtotime($data['bulan_awal']);


        DB::transaction(function () use ($data, $bulanAwalPeriode) {

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

            $rekapPetani = [];

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

                if (!isset($rekapPetani[$petani->id_petani])) {
                    $rekapPetani[$petani->id_petani] = 0;
                }

                $rekapPetani[$petani->id_petani] += $nominalPetani;
            }

            foreach ($rekapPetani as $idPetani => $totalNominalPetani) {

                $saldoTerakhir = Saldo::where('id_petani', $idPetani)
                    ->where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->orderBy('bulan_akhir', 'desc') // 🔑 WAJIB
                    ->lockForUpdate()
                    ->first();

                $bulanInput = date('Y-m', strtotime($data['bulan_awal']));
                $bulanTerakhir = $saldoTerakhir?->bulan_akhir;

                $nextMonth = $bulanTerakhir
                    ? date('Y-m', strtotime('+1 month', strtotime($bulanTerakhir)))
                    : null;

                $selisihBulan = $saldoTerakhir
                    ? (
                        (int) date('Y', strtotime($bulanInput)) * 12 + (int) date('n', strtotime($bulanInput))
                        -
                        ((int) date('Y', strtotime($saldoTerakhir->bulan_awal)) * 12 + (int) date('n', strtotime($saldoTerakhir->bulan_awal)))
                    )
                    : null;

                $periodeSaldo = $saldoTerakhir
                    ? (int) date('n', strtotime($saldoTerakhir->bulan_awal))
                    : null;

                if ($bulanAwalValid) {
                    $totalSaldoLalu = Saldo::where('id_petani', $idPetani) // ✅
                        ->where('id_desa', $data['id_desa'])
                        ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                        ->where('saldo', '>', 0)
                        ->where('bulan_akhir', '<', $data['bulan_awal'])
                        ->sum('saldo');

                    SaldoLalu::firstOrCreate(
                        [
                            'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                            'id_petani' => $idPetani, // ✅
                        ],
                        [
                            'id_desa' => $data['id_desa'],
                            'id_tahun_tanam' => $data['id_tahun_tanam'],
                            'saldo_lalu' => $totalSaldoLalu,
                        ]
                    );
                }

                if (!$saldoTerakhir) {

                    // SALDO PERTAMA
                    $saldo = Saldo::create([
                        'id_petani' => $idPetani,
                        'id_desa' => $data['id_desa'],
                        'id_tahun_tanam' => $data['id_tahun_tanam'],
                        'bulan_awal' => $bulanInput,
                        'bulan_akhir' => $bulanInput,
                        'saldo' => $totalNominalPetani,
                    ]);

                } elseif (
                    $bulanInput === $nextMonth
                    && $selisihBulan === 1
                ) {
                    // EXTEND 2 BULAN SAJA
                    $saldoTerakhir->bulan_akhir = $bulanInput;
                    $saldoTerakhir->saldo += $totalNominalPetani;
                    $saldoTerakhir->save();
                    $saldo = $saldoTerakhir;
                } else {

                    // PERIODE BARU
                    $saldo = Saldo::create([
                        'id_petani' => $idPetani,
                        'id_desa' => $data['id_desa'],
                        'id_tahun_tanam' => $data['id_tahun_tanam'],
                        'bulan_awal' => $bulanInput,
                        'bulan_akhir' => $bulanInput,
                        'saldo' => $totalNominalPetani,
                    ]);
                }

                Transaksi::create([
                    'id_petani' => $idPetani,
                    'id_bagi_bulanan' => $bulan->id_bagi_bulanan,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                    'tipe' => 'credit_bagihasil',
                    'nominal' => $totalNominalPetani,
                    'tanggal' => $data['tanggal_bagi'],
                    'bulan_awal' => $saldo->bulan_awal,
                    'bulan_akhir' => $saldo->bulan_akhir,
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
            'bulan' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('bagi_hasil_bulanan')
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('id_desa', $request->id_desa)
                            ->where('tahun', $request->tahun);
                    })
                    ->ignore($id, 'id_bagi_bulanan'),
            ],

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

            // AMBIL SEMUA PEMBAGIAN PETANI PADA BULAN INI
            $list = BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->get();

            foreach ($list as $d) {

                // AMBIL TRANSAKSI BULAN INI
                $trx = Transaksi::where('id_petani', $d->id_petani)
                    ->where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                    ->where('tipe', 'credit_bagihasil')
                    ->first();

                if (!$trx) {
                    continue; // JIKA TIDAK ADA TRANSAKSI, MAKA LEWATI
                }

                // AMBIL SALDO YANG MENGISI PADA PERIODE INI
                $saldo = Saldo::where('id_petani', $d->id_petani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->where('bulan_awal', '<=', $trx->bulan_awal)
                    ->where('bulan_akhir', '>=', $trx->bulan_akhir)
                    ->lockForUpdate()
                    ->first();
            }

            // HAPUS TRANSAKSI YANG ADA PADA BULAN ITU SAJA
            Transaksi::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)
                ->where('tipe', 'credit_bagihasil')
                ->delete();

            $petaniIds = $list->pluck('id_petani')->unique();

            foreach ($petaniIds as $idPetani) {

                $trxSisa = Transaksi::where('id_petani', $idPetani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->where('tipe', 'credit_bagihasil')
                    ->orderBy('bulan_awal')
                    ->get();

                // AMBIL SALDO AWAL
                $saldoAwal = Saldo::where('id_petani', $idPetani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->where('saldo_awal', '>', 0)
                    ->first();

                // AMBIL SALDO AKUMULASI
                $saldoAkumulasi = Saldo::where('id_petani', $idPetani)
                    ->where('id_desa', $bulan->id_desa)
                    ->where('id_tahun_tanam', $bulan->id_tahun_tanam)
                    ->where('saldo_awal', 0)
                    ->lockForUpdate()
                    ->first();

                // APABILA TIDAK ADA TRANSAKSI TERSISA, HAPUS SALDO
                if ($trxSisa->isEmpty()) {
                    if ($saldoAkumulasi) {
                        $saldoAkumulasi->delete();
                    }
                    continue;
                }

                // BUAT ULANG SALDO
                if (!$saldoAkumulasi) {
                    $saldoAkumulasi = new Saldo();
                    $saldoAkumulasi->id_petani = $idPetani;
                    $saldoAkumulasi->id_desa = $bulan->id_desa;
                    $saldoAkumulasi->id_tahun_tanam = $bulan->id_tahun_tanam;
                    $saldoAkumulasi->saldo_awal = 0;
                }

                $saldoAkumulasi->bulan_awal = $trxSisa->first()->bulan_awal;
                $saldoAkumulasi->bulan_akhir = $trxSisa->last()->bulan_akhir;

                $saldoAkumulasi->saldo =
                    ($saldoAwal->saldo ?? 0) + $trxSisa->sum('nominal');

                $saldoAkumulasi->save();
            }

            // HAPUS DETAIL BAGI HASIL PETANI PADA BULAN INI
            BagiHasilPetani::where('id_bagi_bulanan', $bulan->id_bagi_bulanan)->delete();

            // HAPUS HEADER BULANAN
            $bulan->delete();
        });

        return redirect()
            ->route('bagi-hasil-bulanan.index')
            ->with('success', 'Bagi hasil bulan ini berhasil dihapus');
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

        $totalLuasHa = $petaniData->sum('luas_ha');

        // Generate PDF
        $pdf = Pdf::loadView('bagihasil.pdf_detail', [
            'bulanan' => $bulanan,
            'petaniData' => $petaniData,
            'totalLuasHa' => $totalLuasHa,
        ]);

        $namaBulan = Carbon::create()
            ->month($bulanan->bulan)
            ->locale('id')
            ->translatedFormat('F');

        $namaFile =
            'Bagi Hasil - ' .
            $bulanan->desa->desa . ' - ' .
            $bulanan->tahunTanam->tahun . ' - ' .
            $namaBulan . ' ' .
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

        $bulanIndo = [
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
            12 => 'Desember',
        ];

        $judul = 'Laporan Bagi Hasil';

        // Desa
        if ($request->filled('id_desa') && $bulanan->first()) {
            $judul .= ' - ' . $bulanan->first()->desa->desa;
        } else {
            $judul .= ' - Semua Desa';
        }

        // Tahun Tanam
        if ($request->filled('id_tahun_tanam') && $bulanan->first()) {
            $judul .= ' - TT ' . $bulanan->first()->tahunTanam->tahun;
        }

        // Periode
        if ($request->filled('bulan_start')) {
            [$y, $m] = explode('-', $request->bulan_start);
            $judul .= ' - ' . $bulanIndo[(int) $m] . ' ' . $y;
        }

        if ($request->filled('bulan_end')) {
            [$y, $m] = explode('-', $request->bulan_end);
            $judul .= ' s.d ' . $bulanIndo[(int) $m] . ' ' . $y;
        }

        $judul .= '.pdf';

        // ============================

        $pdf = PDF::loadView('bagihasil.pdf_index', compact('bulanan'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($judul);
    }
}
