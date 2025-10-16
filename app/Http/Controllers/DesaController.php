<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    // Menampilkan semua data desa
    public function index()
    {
        // Ambil semua desa beserta relasi kecamatannya
        $desa = Desa::with('kecamatan')->paginate(10);
        return view('desa.index', compact('desa'));
    }

    // Menampilkan form tambah desa
    public function create()
    {
        $kecamatan = Kecamatan::all();
        return view('desa.create', compact('kecamatan'));
    }

    // Menyimpan data desa baru
    public function store(Request $request)
    {
        $request->validate([
            'desa' => 'required|unique:desa,desa,NULL,id,id_kecamatan,' . $request->id_kecamatan,
            'id_kecamatan' => 'required|exists:kecamatan,id_kecamatan',
        ], [
            'desa.required' => 'Nama desa wajib diisi.',
            'desa.unique' => 'Nama desa sudah ada di kecamatan ini.',
            'id_kecamatan.required' => 'Kecamatan wajib dipilih.',
            'id_kecamatan.exists' => 'Kecamatan tidak valid.',
        ]);

        Desa::create([
            'desa' => $request->desa,
            'id_kecamatan' => $request->id_kecamatan,
        ]);

        return redirect()->route('desa.index')->with('success', 'Data desa berhasil ditambahkan.');
    }

    // Menampilkan form edit desa
    public function edit($id)
    {
        $desa = Desa::findOrFail($id);
        $kecamatan = Kecamatan::all();
        return view('desa.edit', compact('desa', 'kecamatan'));
    }

    // Mengupdate data desa
    public function update(Request $request, $id)
    {
        $request->validate([
            'desa' => 'required|unique:desa,desa,' . $id . ',id_desa,id_kecamatan,' . $request->id_kecamatan,
            'id_kecamatan' => 'required|exists:kecamatan,id_kecamatan',
        ], [
            'desa.required' => 'Nama desa wajib diisi.',
            'desa.unique' => 'Nama desa sudah ada di kecamatan ini.',
            'id_kecamatan.required' => 'Kecamatan wajib dipilih.',
            'id_kecamatan.exists' => 'Kecamatan tidak valid.',
        ]);

        $desa = Desa::findOrFail($id);
        $desa->update([
            'desa' => $request->desa,
            'id_kecamatan' => $request->id_kecamatan,
        ]);

        return redirect()->route('desa.index')->with('success', 'Data desa berhasil diperbarui.');
    }

    // Menghapus data desa
    public function destroy($id)
    {
        $desa = Desa::findOrFail($id);
        $desa->delete();

        return redirect()->route('desa.index')->with('success', 'Data desa berhasil dihapus.');
    }
}
