<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    // Menampilkan semua data kecamatan
    public function index()
    {
        $kecamatan = Kecamatan::withCount('desa')
            ->orderBy('id_kecamatan', 'desc')
            ->get();

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
        ]);

        $kecamatan = Kecamatan::findOrFail($id);
        $kecamatan->update([
            'kecamatan' => $request->kecamatan
        ]);

        return redirect()->route('kecamatan.index')->with('success', 'Data kecamatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kecamatan = Kecamatan::withCount('desa')->findOrFail($id);

        // Kalau masih dipakai
        if ($kecamatan->desa_count > 0) {
            return back()->with('error', 'Kecamatan Sedang Digunakan!');
        }

        $kecamatan->delete();

        return back()->with('success', 'Data kecamatan berhasil dihapus.');
    }
}
