<?php

namespace App\Http\Controllers;

use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;

class TahunTanamController extends Controller
{
    // Menampilkan semua data tahun
    public function index()
    {
        $tahun_tanam = Tahun_Tanam::withCount('lahan')
            ->orderBy('id_tahun_tanam', 'desc')
            ->get();

        return view('tahun_tanam.index', compact('tahun_tanam'));
    }

    // Menampilkan form tambah tahun
    public function create()
    {
        return view('tahun_tanam.create');
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4|unique:tahun_tanam,tahun',
        ]);

        Tahun_Tanam::create([
            'tahun' => $request->tahun,
        ]);

        return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun berhasil disimpan!');
    }

    // Menampilkan form edit tahun
    public function edit($id)
    {
        $tahun_tanam = Tahun_Tanam::findOrFail($id);
        return view('tahun_tanam.edit', compact('tahun_tanam'));
    }

    // Mengupdate data tahun
    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|digits:4|unique:tahun_tanam,tahun,' . $id . ',id_tahun_tanam',
        ]);

        $tahun_tanam = Tahun_Tanam::findOrFail($id);
        $tahun_tanam->update([
            'tahun' => $request->tahun
        ]);

        return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun berhasil diperbarui.');
    }

    // Menghapus data tahun
    public function destroy($id)
    {
        $tahun_tanam = Tahun_Tanam::withCount('lahan')->findOrFail($id);

        if ($tahun_tanam->lahan_count > 0) {
            return back()->with('error', 'Tahun tanam Sedang Digunakan!');
        }

        $tahun_tanam->delete();

        return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun tanam berhasil dihapus.');
    }
}
