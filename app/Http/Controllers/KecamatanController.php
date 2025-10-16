<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    // Menampilkan semua data kecamatan
    public function index()
    {
        $kecamatan = Kecamatan::all();
        return view('kecamatan.index', compact('kecamatan'));
    }

    // Menampilkan form tambah kecamatan
    public function create()
    {
        return view('kecamatan.create');
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'kecamatan' => 'required|unique:kecamatan,kecamatan',
        ], [
            'kecamatan.required' => 'Nama kecamatan wajib diisi.',
            'kecamatan.unique' => 'Nama kecamatan sudah ada, silakan masukkan nama lain.',
        ]);

        Kecamatan::create([
            'kecamatan' => $request->kecamatan
        ]);

        return redirect()->route('kecamatan.index')->with('success', 'Data kecamatan berhasil ditambahkan.');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        return view('kecamatan.edit', compact('kecamatan'));
    }

    // Mengupdate data kecamatan
    public function update(Request $request, $id)
    {
        $request->validate([
            // validasi unique, tapi abaikan data yang sedang diedit
            'kecamatan' => 'required|unique:kecamatan,kecamatan,' . $id . ',id_kecamatan',
        ], [
            'kecamatan.required' => 'Nama kecamatan wajib diisi.',
            'kecamatan.unique' => 'Nama kecamatan sudah ada, silakan masukkan nama lain.',
        ]);

        $kecamatan = Kecamatan::findOrFail($id);
        $kecamatan->update([
            'kecamatan' => $request->kecamatan
        ]);

        return redirect()->route('kecamatan.index')->with('success', 'Data kecamatan berhasil diperbarui.');
    }

    // Menghapus data kecamatan
    public function destroy($id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        $kecamatan->delete();

        return redirect()->route('kecamatan.index')->with('success', 'Data kecamatan berhasil dihapus.');
    }
}
