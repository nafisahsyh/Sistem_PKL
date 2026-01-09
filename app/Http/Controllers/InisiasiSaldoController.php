<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saldo;
use App\Models\InisiasiSaldo;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Support\Facades\DB;

class InisiasiSaldoController extends Controller
{

    public function index(Request $request)
    {
        if (!$request->hasAny(['id_desa', 'id_tahun_tanam', 'search'])) {

            $default = InisiasiSaldo::orderByDesc('created_at')->first();

            if ($default) {
                return redirect()->route('inisiasi-saldo.index', [
                    'id_desa' => $default->id_desa,
                    'id_tahun_tanam' => $default->id_tahun_tanam,
                ]);
            }
        }

        $desa = Desa::orderBy('desa')->get();
        $tahunTanam = Tahun_Tanam::orderBy('tahun')->get();

        // Periode inisiasi
        $bulanInisiasiAwal = '2024-11';
        $bulanInisiasiAkhir = '2024-12';

        $sistemBerjalan = false;

        if ($request->filled('id_desa') && $request->filled('id_tahun_tanam')) {
            $sistemBerjalan = Saldo::where('id_desa', $request->id_desa)
                ->where('id_tahun_tanam', $request->id_tahun_tanam)
                ->where(function ($q) {
                    $q->where('bulan_awal', '!=', '2024-11')
                        ->orWhere('bulan_akhir', '!=', '2024-12');
                })
                ->exists();
        }
        $query = InisiasiSaldo::query();

        // Filter desa (pakai snapshot string atau id)
        if ($request->filled('id_desa')) {
            $query->where('id_desa', $request->id_desa);
        }

        // Filter tahun tanam
        if ($request->filled('id_tahun_tanam')) {
            $query->where('id_tahun_tanam', $request->id_tahun_tanam);
        }

        // Search nama / nomor plasma (snapshot)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_petani', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_plasma', 'like', '%' . $request->search . '%');
            });
        }

        $data = $query
            ->orderBy('nomor_plasma')
            ->paginate(15)
            ->withQueryString();

        return view('inisiasi_saldo.index', compact(
            'desa',
            'tahunTanam',
            'data',
            'sistemBerjalan'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'id_desa' => 'required|exists:desa,id_desa',
            'id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'saldo' => 'required',
        ]);

        // Bersihkan format rupiah
        $saldo = (int) str_replace(['Rp', '.', ' '], '', $request->saldo);

        // Periode saldo
        $bulanAwal = '2024-11';
        $bulanAkhir = '2024-12';

        // Cek apakah saldo awal sudah ada untuk periode tsb
        $cek = Saldo::where('id_petani', $request->id_petani)
            ->where('id_desa', $request->id_desa)
            ->where('id_tahun_tanam', $request->id_tahun_tanam)
            ->where('bulan_awal', $bulanAwal)
            ->where('bulan_akhir', $bulanAkhir)
            ->first();

        if ($cek) {
            return redirect()->back()
                ->with('error', 'Saldo awal sudah diinisiasi.');
        }

        Saldo::create([
            'id_petani' => $request->id_petani,
            'id_desa' => $request->id_desa,
            'id_tahun_tanam' => $request->id_tahun_tanam,
            'bulan_awal' => $bulanAwal,
            'bulan_akhir' => $bulanAkhir,
            'saldo' => $saldo,
            'saldo_awal' => $saldo,
        ]);

        return redirect()->back()
            ->with('success', 'Saldo awal petani berhasil ditambahkan.');
    }

    public function storePetani(Request $request)
    {
        $request->validate([
            'id_desa' => 'required|exists:desa,id_desa',
            'id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
        ]);

        // Pengecekan apakah sudah pernah diinisiasi atau belum
        $sudahAda = InisiasiSaldo::where('id_desa', $request->id_desa)
            ->where('id_tahun_tanam', $request->id_tahun_tanam)
            ->exists();

        if ($sudahAda) {
            return back()->with(
                'warning',
                'Data inisiasi saldo untuk desa dan tahun tanam ini sudah ada. Silakan pilih yang lain.'
            );
        }

        // Ambil semua petani AKTIF di desa & tahun tanam
        $petaniList = DB::table('detail_kepemilikan')
            ->join('kepemilikan', 'kepemilikan.id_kepemilikan', '=', 'detail_kepemilikan.id_kepemilikan')
            ->join('petani', 'petani.id_petani', '=', 'kepemilikan.id_petani')
            ->join('lahan', 'lahan.id_lahan', '=', 'detail_kepemilikan.id_lahan')
            ->where('detail_kepemilikan.status_kepemilikan', 'aktif')
            ->where('lahan.id_desa', $request->id_desa)
            ->where('lahan.id_tahun_tanam', $request->id_tahun_tanam)
            ->select(
                'petani.id_petani',
                'petani.nomor_anggota_plasma',
                'petani.nomor_anggota_koperasi',
                'petani.nama',
                'lahan.id_desa',
                'lahan.id_tahun_tanam'
            )
            ->distinct()
            ->get();

        if ($petaniList->isEmpty()) {
            return back()->with('error', 'Tidak ada petani aktif pada desa dan tahun tanam tersebut.');
        }

        // Ambil snapshot desa & tahun
        $desa = DB::table('desa')->where('id_desa', $request->id_desa)->first();
        $tahun = DB::table('tahun_tanam')->where('id_tahun_tanam', $request->id_tahun_tanam)->first();

        $inserted = 0;

        DB::beginTransaction();
        try {

            foreach ($petaniList as $p) {

                $exists = InisiasiSaldo::where('id_petani', $p->id_petani)
                    ->where('id_desa', $request->id_desa)
                    ->where('id_tahun_tanam', $request->id_tahun_tanam)
                    ->exists();

                if ($exists) {
                    continue;
                }

                InisiasiSaldo::create([
                    'id_petani' => $p->id_petani,
                    'id_desa' => $request->id_desa,
                    'id_tahun_tanam' => $request->id_tahun_tanam,

                    'nomor_plasma' => $p->nomor_anggota_plasma,
                    'nomor_koperasi' => $p->nomor_anggota_koperasi,
                    'nama_petani' => $p->nama,
                    'desa' => $desa->desa,
                    'tahun_tanam' => $tahun->tahun,
                ]);

                $inserted++;
            }

            DB::commit();

            if ($inserted === 0) {
                return back()->with(
                    'error',
                    'Data inisiasi saldo untuk desa dan tahun tanam ini sudah ada.'
                );
            }

            return back()->with(
                'success',
                "Berhasil membuat data inisiasi saldo untuk {$inserted} petani."
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Saldo $saldo)
    {
        // cek sistem berjalan
        $sistemBerjalan = Saldo::where('id_desa', $saldo->id_desa)
            ->where('id_tahun_tanam', $saldo->id_tahun_tanam)
            ->where(function ($q) {
                $q->where('bulan_awal', '!=', '2024-11')
                    ->orWhere('bulan_akhir', '!=', '2024-12');
            })
            ->exists();

        if ($sistemBerjalan) {
            return back()->with(
                'error',
                'Saldo tidak dapat dihapus karena sistem sudah berjalan.'
            );
        }

        $saldo->delete();

        return back()->with('success', 'Saldo awal berhasil dihapus.');
    }

    public function update(Request $request, Saldo $saldo)
    {
        // Cek sistem berjalan (global desa & tahun tanam)
        $sistemBerjalan = Saldo::where('id_desa', $saldo->id_desa)
            ->where('id_tahun_tanam', $saldo->id_tahun_tanam)
            ->where(function ($q) {
                $q->where('bulan_awal', '!=', '2024-11')
                    ->orWhere('bulan_akhir', '!=', '2024-12');
            })
            ->exists();

        if ($sistemBerjalan) {
            return back()->with('error', 'Saldo tidak dapat diedit karena sistem sudah berjalan.');
        }

        // pastikan ini saldo awal
        if (
            $saldo->bulan_awal !== '2024-11' ||
            $saldo->bulan_akhir !== '2024-12'
        ) {
            return back()->with('error', 'Hanya saldo awal yang boleh diedit.');
        }

        $request->validate([
            'saldo' => 'required'
        ]);

        $nilai = (int) str_replace(['Rp', '.', ' '], '', $request->saldo);

        $saldo->update([
            'saldo' => $nilai,
            'saldo_awal' => $nilai
        ]);

        return back()->with('success', 'Saldo awal berhasil diperbarui.');
    }

}
