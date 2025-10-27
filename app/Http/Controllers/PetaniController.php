<?php

namespace App\Http\Controllers;

use App\Models\Petani;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\Kepemilikan;
use App\Models\DetailKepemilikan;
use App\Models\Lahan;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetaniController extends Controller
{
    public function index(Request $request)
    {
        $query = Petani::withCount('kepemilikan');

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
            'pdf_scan_kk' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $ktpName = null;
        $kkName = null;

        if ($request->hasFile('pdf_scan_ktp')) {
            $ktpName = time() . '_' . $request->file('pdf_scan_ktp')->getClientOriginalName();
            $request->file('pdf_scan_ktp')->storeAs('ktp_pdf', $ktpName, 'public');
        }

        if ($request->hasFile('pdf_scan_kk')) {
            $kkName = time() . '_' . $request->file('pdf_scan_kk')->getClientOriginalName();
            $request->file('pdf_scan_kk')->storeAs('ktp_pdf', $kkName, 'public');
        }

        Petani::create([
            'nomor_anggota_plasma' => $request->nomor_anggota_plasma,
            'nomor_anggota_koperasi' => $request->nomor_anggota_koperasi,
            'NIK' => $request->NIK,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'pdf_scan_ktp' => $ktpName,
            'pdf_scan_kk' => $kkName,
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
            'pdf_scan_kk' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $pdfName = $petani->pdf_scan_ktp;

        // Jika user upload file baru, hapus yang lama
        if ($request->hasFile('pdf_scan_ktp')) {
            if ($pdfName && Storage::disk('public')->exists('ktp_pdf/' . $pdfName)) {
                Storage::disk('public')->delete('ktp_pdf/' . $pdfName);
            }

            $pdfName = time() . '_' . $request->file(key: 'pdf_scan_ktp')->getClientOriginalName();
            $request->file('pdf_scan_ktp')->storeAs('ktp_pdf', $pdfName, 'public');
        }

        $pdfName = $petani->pdf_scan_kk;

        // Jika user upload file baru, hapus yang lama
        if ($request->hasFile('pdf_scan_kk')) {
            if ($pdfName && Storage::disk('public')->exists('ktp_pdf/' . $pdfName)) {
                Storage::disk('public')->delete('ktp_pdf/' . $pdfName);
            }

            $pdfName = time() . '_' . $request->file('pdf_scan_kk')->getClientOriginalName();
            $request->file(key: 'pdf_scan_kk')->storeAs('ktp_pdf', $pdfName, 'public');
        }

        $petani->update([
            'nomor_anggota_plasma' => $request->nomor_anggota_plasma,
            'nomor_anggota_koperasi' => $request->nomor_anggota_koperasi,
            'NIK' => $request->NIK,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'pdf_scan_ktp' => $pdfName,
            'pdf_scan_kk' => $pdfName,
        ]);

        return redirect()->route('petani.index')->with('success', 'Data petani berhasil diperbarui.');
    }

    public function destroy(Petani $petani)
    {
        // Hapus file PDF jika ada
        if ($petani->pdf_scan_ktp && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_ktp)) {
            Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_ktp);
        }

        if ($petani->pdf_scan_kk && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_kk)) {
            Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_kk);
        }

        $petani->delete();

        return redirect()->route('petani.index')->with('success', 'Data petani berhasil dihapus.');
    }

    public function tambahKepemilikan($id_petani)
    {
        $petani = Petani::with('desa.kecamatan')->findOrFail($id_petani);
        $desa = Desa::with(relations: 'kecamatan')->get();
        $tahun_tanam = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        return view('petani.createkepemilikan', compact('petani', 'desa', 'tahun_tanam'));
    }

    public function storeKepemilikan(Request $request)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',

            'lahan' => 'required|array|min:1',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.luas_peta' => 'required|numeric|min:0',
            'lahan.*.nomor_SHM' => 'nullable|string|max:100',
            'lahan.*.nama_SHM' => 'nullable|string|max:100',
            'lahan.*.nomor_sporadik' => 'nullable|string|max:100',
            'lahan.*.nama_sporadik' => 'nullable|string|max:100',
            'lahan.*.nomor_kavling' => 'nullable|string|max:100',
            'lahan.*.luas_surat' => 'nullable|numeric|min:0',
            'lahan.*.nomor_pbb' => 'nullable|string|max:100',
            'lahan.*.jumlah_pbb' => 'nullable|numeric|min:0',
            'lahan.*.pdf_scan_shm' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.pdf_scan_peta' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.status_kepemilikan' => 'required|in:aktif,nonaktif',
            'lahan.*.tanggal_mulai' => 'nullable|date',
            'lahan.*.tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();

        try {
            // Simpan data utama kepemilikan
            $kepemilikan = Kepemilikan::create([
                'id_petani' => $request->id_petani,
            ]);

            // Simpan per lahan
            foreach ($request->lahan as $index => $lahanData) {
                // Simpan ke tabel lahan
                $lahan = Lahan::create([
                    'id_desa' => $lahanData['id_desa'],
                    'id_tahun_tanam' => $lahanData['id_tahun_tanam'],
                    'luas_peta' => $lahanData['luas_peta'],
                ]);

                // Simpan file SHM & Peta (jika ada)
                $shmName = null;
                $petaName = null;

                // File SHM
                if (isset($lahanData['pdf_scan_shm']) && $lahanData['pdf_scan_shm']->isValid()) {
                    $shmPath = $lahanData['pdf_scan_shm']->store('shm_pdf', 'public');
                } else {
                    $shmPath = $detail->pdf_scan_shm ?? null;
                }

                // File Peta
                if (isset($lahanData['pdf_scan_peta']) && $lahanData['pdf_scan_peta']->isValid()) {
                    $petaPath = $lahanData['pdf_scan_peta']->store('peta_pdf', 'public');
                } else {
                    $petaPath = $detail->pdf_scan_peta ?? null;
                }
                // Simpan detail kepemilikan
                DetailKepemilikan::create([
                    'id_kepemilikan' => $kepemilikan->id_kepemilikan,
                    'id_lahan' => $lahan->id_lahan,
                    'nomor_SHM' => $lahanData['nomor_SHM'] ?? null,
                    'nama_SHM' => $lahanData['nama_SHM'] ?? null,
                    'nomor_sporadik' => $lahanData['nomor_sporadik'] ?? null,
                    'nama_sporadik' => $lahanData['nama_sporadik'] ?? null,
                    'nomor_kavling' => $lahanData['nomor_kavling'] ?? null,
                    'luas_surat' => $lahanData['luas_surat'] ?? null,
                    'nomor_pbb' => $lahanData['nomor_pbb'] ?? null,
                    'jumlah_pbb' => $lahanData['jumlah_pbb'] ?? null,
                    'pdf_scan_shm' => $shmName,
                    'pdf_scan_peta' => $petaName,
                    'status_kepemilikan' => $lahanData->status_kepemilikan,
                    'tanggal_mulai' => $lahanData->tanggal_mulai,
                    'tanggal_selesai' => $lahanData->tanggal_selesai,
                ]);
            }

            DB::commit();
            return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


}
