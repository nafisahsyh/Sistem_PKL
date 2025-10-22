<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kepemilikan;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\Petani;
use App\Models\Lahan;
use App\Models\DetailKepemilikan;
use Illuminate\Support\Facades\DB;

class KepemilikanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kepemilikan::with(['petani', 'detailKepemilikan.lahan']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->whereHas('petani', function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('NIK', 'like', "%{$keyword}%");
            })
            ->orWhereHas('detailKepemilikan', function ($q) use ($keyword) {
                $q->where('nomor_SHM', 'like', "%{$keyword}%")
                  ->orWhere('nomor_pbb', 'like', "%{$keyword}%");
            });
        }

        $kepemilikan = $query->orderBy('id_kepemilikan', 'desc')->paginate(10);

        return view('kepemilikan.index', compact('kepemilikan'));
    }

    public function create()
    {
        $petani = Petani::with('desa.kecamatan')->get();
        $desa = Desa::with('kecamatan')->get();
        $tahun_tanam = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        return view('kepemilikan.create', compact('petani', 'desa', 'tahun_tanam'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'status_kepemilikan' => 'required|in:aktif,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

            'lahan' => 'required|array|min:1',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.luas_peta' => 'required|numeric|min:0',
            'lahan.*.nomor_SHM' => 'nullable|string|max:100',
            'lahan.*.nomor_sporadik' => 'nullable|string|max:100',
            'lahan.*.luas_surat' => 'nullable|numeric|min:0',
            'lahan.*.nomor_pbb' => 'nullable|string|max:100',
            'lahan.*.jumlah_pbb' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Simpan data utama kepemilikan
            $kepemilikan = Kepemilikan::create([
                'id_petani' => $request->id_petani,
                'status_kepemilikan' => $request->status_kepemilikan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            // Simpan lahan dan detail kepemilikan
            foreach ($request->lahan as $lahanData) {
                $lahan = Lahan::create([
                    'id_desa' => $lahanData['id_desa'],
                    'id_tahun_tanam' => $lahanData['id_tahun_tanam'],
                    'luas_peta' => $lahanData['luas_peta'],
                ]);

                DetailKepemilikan::create([
                    'id_kepemilikan' => $kepemilikan->id_kepemilikan,
                    'id_lahan' => $lahan->id_lahan,
                    'nomor_SHM' => $lahanData['nomor_SHM'] ?? null,
                    'nomor_sporadik' => $lahanData['nomor_sporadik'] ?? null,
                    'luas_surat' => $lahanData['luas_surat'] ?? null,
                    'nomor_pbb' => $lahanData['nomor_pbb'] ?? null,
                    'jumlah_pbb' => $lahanData['jumlah_pbb'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahun_tanam'
        ])->findOrFail($id);

        return view('kepemilikan.detail', compact('kepemilikan'));
    }

    public function destroy($id)
    {
        $kepemilikan = Kepemilikan::findOrFail($id);
        $kepemilikan->delete();

        return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil dihapus.');
    }
}
