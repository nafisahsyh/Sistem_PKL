<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BagiHasilBulanan;
use App\Models\BagiHasilPeriode;
use App\Models\BagiHasilPetani;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\DetailKepemilikan;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use DB;

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

        // ORDER BY → tahun terbaru, bulan terbaru
        $bulanan = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->paginate(20);

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
}

