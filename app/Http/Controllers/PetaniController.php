<?php

namespace App\Http\Controllers;

use App\Models\Petani;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetaniController extends Controller
{
    public function index(Request $request)
    {
        $query = Petani::query();

        // Fitur search
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('NIK', 'like', "%{$keyword}%")
                    ->orWhere('nomor_anggota_plasma', 'like', "%{$keyword}%")
                    ->orWhere('nomor_anggota_koperasi', 'like', "%{$keyword}%");
            });
        }

        $petani = $query->orderBy('nama')->paginate(10);

        return view('petani.index', compact('petani'));
    }

    public function create()
    {
        return view('petani.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_anggota_plasma' => 'required|string|max:100|unique:petani',
            'nomor_anggota_koperasi' => 'required|string|max:100|unique:petani',
            'NIK' => 'required|string|size:16|unique:petani',
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
            'pdf_scan_ktp' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $pdfName = null;

        // Upload PDF ke storage/app/public/ktp_pdf
        if ($request->hasFile('pdf_scan_ktp')) {
            $pdfName = time() . '_' . $request->file('pdf_scan_ktp')->getClientOriginalName();
            $request->file('pdf_scan_ktp')->storeAs('ktp_pdf', $pdfName, 'public'); // ✅ simpan di disk 'public'
        }

        Petani::create([
            'nomor_anggota_plasma' => $request->nomor_anggota_plasma,
            'nomor_anggota_koperasi' => $request->nomor_anggota_koperasi,
            'NIK' => $request->NIK,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'pdf_scan_ktp' => $pdfName,
        ]);

        return redirect()->route('petani.index')->with('success', 'Data petani berhasil ditambahkan.');
    }

    public function edit(Petani $petani)
    {
        return view('petani.edit', compact('petani'));
    }

    public function update(Request $request, Petani $petani)
    {
        $request->validate([
            'nomor_anggota_plasma' => 'required|string|max:100|unique:petani,nomor_anggota_plasma,' . $petani->id_petani . ',id_petani',
            'nomor_anggota_koperasi' => 'required|string|max:100|unique:petani,nomor_anggota_koperasi,' . $petani->id_petani . ',id_petani',
            'NIK' => 'required|string|size:16|unique:petani,NIK,' . $petani->id_petani . ',id_petani',
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
            'pdf_scan_ktp' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $pdfName = $petani->pdf_scan_ktp;

        // Jika user upload file baru, hapus yang lama
        if ($request->hasFile('pdf_scan_ktp')) {
            if ($pdfName && Storage::disk('public')->exists('ktp_pdf/' . $pdfName)) {
                Storage::disk('public')->delete('ktp_pdf/' . $pdfName);
            }

            $pdfName = time() . '_' . $request->file('pdf_scan_ktp')->getClientOriginalName();
            $request->file('pdf_scan_ktp')->storeAs('ktp_pdf', $pdfName, 'public');
        }

        $petani->update([
            'nomor_anggota_plasma' => $request->nomor_anggota_plasma,
            'nomor_anggota_koperasi' => $request->nomor_anggota_koperasi,
            'NIK' => $request->NIK,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'pdf_scan_ktp' => $pdfName,
        ]);

        return redirect()->route('petani.index')->with('success', 'Data petani berhasil diperbarui.');
    }

    public function destroy(Petani $petani)
    {
        // Hapus file PDF jika ada
        if ($petani->pdf_scan_ktp && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_ktp)) {
            Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_ktp);
        }

        $petani->delete();

        return redirect()->route('petani.index')->with('success', 'Data petani berhasil dihapus.');
    }
}
