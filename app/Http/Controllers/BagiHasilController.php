<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;
use App\Models\BagiHasilPetani;
use App\Models\BagiHasilBulanan;
use App\Models\BagiHasilPeriode;
use App\Models\DetailKepemilikan;
use Illuminate\Support\Facades\DB;

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

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('desa', function ($q2) use ($search) {
                    $q2->where('desa', 'like', "%$search%");
                })
                    ->orWhereHas('tahunTanam', function ($q2) use ($search) {
                        $q2->where('tahun', 'like', "%$search%");
                    });
            });
        }

        // ORDER BY → tahun terbaru, bulan terbaru
        $bulanan = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->paginate(20)
            ->withQueryString(); // biar search & filter tetap saat pagination

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
        $data = $request->validate([
            'id_desa' => 'required|integer',
            'id_tahun_tanam' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'tanggal_bagi' => 'required|date',
            'total_bagian' => 'required|numeric',
        ]);

        DB::transaction(function () use ($data) {

            // Simpan data bulanan
            $bulan = BagiHasilBulanan::create($data);

            // Cari bulan sebelumnya
            $prevMonth = $data['bulan'] - 1;
            if ($prevMonth >= 1) {
                $bulanSebelumnya = BagiHasilBulanan::where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->where('bulan', $prevMonth)
                    ->where('tahun', $data['tahun'])
                    ->first();
            }

            // Cek periode
            if (!empty($bulanSebelumnya)) {
                $periodeExist = BagiHasilPeriode::where('id_desa', $data['id_desa'])
                    ->where('id_tahun_tanam', $data['id_tahun_tanam'])
                    ->where('bulan_awal', $prevMonth)
                    ->where('bulan_akhir', $data['bulan'])
                    ->where('tahun', $data['tahun'])
                    ->exists();
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

            // Ambil petani aktif KSM melalui DetailKepemilikan
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

            // Hitung total luas lahan
            // Hitung total luas lahan (dari m² ke Ha)
            $totalLuasHa = $kelola->sum(fn($item) => ($item->lahan->luas_peta ?? 0) / 10000);

            if ($totalLuasHa == 0) {
                throw new \Exception('Total luas lahan 0, bagi hasil tidak bisa diproses.');
            }

            foreach ($kelola as $kepemilikan) {
                // Luas tiap petani dalam Ha
                $luasHa = ($kepemilikan->lahan->luas_peta ?? 0) / 10000;

                // Hitung nominal berdasarkan Ha
                $nominalPetani = ($data['total_bagian'] / $totalLuasHa) * $luasHa;

                $petaniId = $kepemilikan->kepemilikan->id_petani;

                BagiHasilPetani::create([
                    'id_bagi_periode' => $periode->id_bagi_periode,
                    'id_petani' => $petaniId,
                    'total_luas_ksm' => $luasHa, // simpan dalam Ha
                    'total_nominal' => $nominalPetani,
                ]);

                $saldo = Saldo::firstOrCreate([
                    'id_petani' => $petaniId,
                    'id_desa' => $data['id_desa'],
                    'id_tahun_tanam' => $data['id_tahun_tanam'],
                ]);

                $saldo->saldo += $nominalPetani;
                $saldo->save();

                Transaksi::create([
                    'id_petani' => $petaniId,
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
        $data = $request->validate([
            'id_desa' => 'required|integer',
            'id_tahun_tanam' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'tanggal_bagi' => 'required|date',
            'total_bagian' => 'required|numeric',
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
}
