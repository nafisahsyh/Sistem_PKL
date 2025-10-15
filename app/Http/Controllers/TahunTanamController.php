<?php

namespace App\Http\Controllers;

use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;

class TahunTanamController extends Controller
{
    // Menampilkan semua data kecamatan
    public function index()
    {
        $tahun_tanam = Tahun_Tanam::all();
        return view('tahun_tanam.index', compact('tahun_tanam'));
    }

    // Menampilkan form tambah kecamatan
    public function create()
    {
        return view('tahun_tanam.create');
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
    $request->validate([
        'tahun' => 'required|digits:4',
    ]);

    Tahun_Tanam::create([
        'tahun' => $request->tahun,
    ]);

    return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun berhasil disimpan!');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $tahun_tanam = Tahun_Tanam::findOrFail($id);
        return view('tahun_tanam.edit', compact('tahun_tanam'));
    }

    // Mengupdate data kecamatan
    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
        ]);

        $tahun_tanam = Tahun_Tanam::findOrFail($id);
        $tahun_tanam->update([
            'tahun' => $request->tahun
        ]);

        return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun berhasil diperbarui.');
    }

    // Menghapus data kecamatan
    public function destroy($id)
    {
        $tahun_tanam = Tahun_Tanam::findOrFail($id);
        $tahun_tanam->delete();

        return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun berhasil dihapus.');
    }
}
