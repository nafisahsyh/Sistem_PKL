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
        $query = Desa::with('kecamatan')->withCount('lahan');

        if ($request->has('search') && !empty($request->search)) {
            $keyword = $request->search;
            $query->where('desa', 'like', "%{$keyword}%")
                ->orWhereHas('kecamatan', function ($q) use ($keyword) {
                    $q->where('kecamatan', 'like', "%{$keyword}%");
                });
        }

        $desa = $query->orderBy('id_desa', 'desc')
            ->paginate(20)
            ->withQueryString();

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
        $desa = Desa::withCount('lahan')->findOrFail($id);

        if ($desa->lahan_count > 0) {
            return back()->with('error', 'Desa Sedang Digunakan!');
        }

        $desa->delete();

        return redirect()->route('desa.index')->with('success', 'Data desa berhasil dihapus.');
    }
}
