<?php

namespace App\Http\Controllers;

use App\Models\Tahun_Tanam;
use Illuminate\Http\Request;

class TahunTanamController extends Controller
{
    // Menampilkan semua data tahun
    public function index()
    {
        $tahun_tanam = Tahun_Tanam::orderBy('id_tahun_tanam', 'desc')->get();
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
        ], [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.digits' => 'Tahun harus 4 digit.',
            'tahun.unique' => 'Tahun sudah ada, silakan masukkan tahun lain.',
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
        ], [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.digits' => 'Tahun harus 4 digit.',
            'tahun.unique' => 'Tahun sudah ada, silakan masukkan tahun lain.',
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
        $tahun_tanam = Tahun_Tanam::findOrFail($id);
        $tahun_tanam->delete();

        return redirect()->route('tahun_tanam.index')->with('success', 'Data tahun berhasil dihapus.');
    }
}
