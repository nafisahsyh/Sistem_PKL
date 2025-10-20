<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    // Menampilkan semua data desa + search
    public function index(Request $request)
    {
        $query = Desa::with('kecamatan');

        // Filter jika ada keyword search
        if ($request->has('search') && !empty($request->search)) {
            $keyword = $request->search;
            $query->where('desa', 'like', "%{$keyword}%")
                ->orWhereHas('kecamatan', function($q) use ($keyword) {
                    $q->where('kecamatan', 'like', "%{$keyword}%");
                });
        }

       $desa = $query->orderBy('id_desa', 'desc')->paginate(10)->withQueryString(); // keep search in pagination links

        return view('desa.index', compact('desa'));
    }

    // Form tambah desa
    public function create()
    {
        $kecamatan = Kecamatan::all();
        return view('desa.create', compact('kecamatan'));
    }

    // Simpan data desa baru
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

    // Form edit desa
    public function edit($id)
    {
        $desa = Desa::findOrFail($id);
        $kecamatan = Kecamatan::all();
        return view('desa.edit', compact('desa', 'kecamatan'));
    }

    // Update data desa
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

    // Hapus data desa
    public function destroy($id)
    {
        $desa = Desa::findOrFail($id);
        $desa->delete();

        return redirect()->route('desa.index')->with('success', 'Data desa berhasil dihapus.');
    }
}
