<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kepemilikan;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\Petani;
use App\Models\Lahan;

class KepemilikanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kepemilikan::with(['petani', 'lahan']);

        // Fitur search berdasarkan nama petani atau nomor SHM / PBB
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->whereHas('petani', function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('NIK', 'like', "%{$keyword}%");
            })
            ->orWhere('nomor_SHM', 'like', "%{$keyword}%")
            ->orWhere('nomor_pbb', 'like', "%{$keyword}%");
        }

        $kepemilikan = $query->orderBy('id_kepemilikan', 'desc')->paginate(10);

        return view('kepemilikan.index', compact('kepemilikan'));
    }

    public function create()
    {
        $petani = Petani::orderBy('nama')->get();
        $lahan = Lahan::orderBy('id_lahan')->get(); // Pastikan kolom nama_lahan ada di tabel lahan
        return view('kepemilikan.create', compact('petani', 'lahan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'id_lahan' => 'required|exists:lahan,id_lahan',
            'nomor_SHM' => 'nullable|string|max:100|unique:kepemilikan',
            'nomor_sporadik' => 'nullable|string|max:100|unique:kepemilikan',
            'luas_surat' => 'required|numeric|min:0',
            'nomor_pbb' => 'required|string|max:100|unique:kepemilikan',
            'jumlah_pbb' => 'required|numeric|min:0',
            'status_kepemilikan' => 'required|in:aktif,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        Kepemilikan::create([
            'id_petani' => $request->id_petani,
            'id_lahan' => $request->id_lahan,
            'nomor_SHM' => $request->nomor_SHM,
            'nomor_sporadik' => $request->nomor_sporadik,
            'luas_surat' => $request->luas_surat,
            'nomor_pbb' => $request->nomor_pbb,
            'jumlah_pbb' => $request->jumlah_pbb,
            'status_kepemilikan' => $request->status_kepemilikan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil ditambahkan.');
    }

    public function edit(Kepemilikan $kepemilikan)
    {
        $petani = Petani::orderBy('nama')->get();
        $lahan = Lahan::orderBy('nama_lahan')->get();
        return view('kepemilikan.edit', compact('kepemilikan', 'petani', 'lahan'));
    }

    public function update(Request $request, Kepemilikan $kepemilikan)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'id_lahan' => 'required|exists:lahan,id_lahan',
            'nomor_SHM' => 'nullable|string|max:100|unique:kepemilikan,nomor_SHM,' . $kepemilikan->id_kepemilikan . ',id_kepemilikan',
            'nomor_sporadik' => 'nullable|string|max:100|unique:kepemilikan,nomor_sporadik,' . $kepemilikan->id_kepemilikan . ',id_kepemilikan',
            'luas_surat' => 'required|numeric|min:0',
            'nomor_pbb' => 'required|string|max:100|unique:kepemilikan,nomor_pbb,' . $kepemilikan->id_kepemilikan . ',id_kepemilikan',
            'jumlah_pbb' => 'required|numeric|min:0',
            'status_kepemilikan' => 'required|in:aktif,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $kepemilikan->update([
            'id_petani' => $request->id_petani,
            'id_lahan' => $request->id_lahan,
            'nomor_SHM' => $request->nomor_SHM,
            'nomor_sporadik' => $request->nomor_sporadik,
            'luas_surat' => $request->luas_surat,
            'nomor_pbb' => $request->nomor_pbb,
            'jumlah_pbb' => $request->jumlah_pbb,
            'status_kepemilikan' => $request->status_kepemilikan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil diperbarui.');
    }

    public function destroy(Kepemilikan $kepemilikan)
    {
        $kepemilikan->delete();

        return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil dihapus.');
    }
}
