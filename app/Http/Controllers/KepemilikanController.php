<?php

namespace App\Http\Controllers;

use App\Models\Kepemilikan;
use App\Models\DetailKepemilikan;
use App\Models\Petani;
use App\Models\Lahan;
use App\Models\Desa;
use App\Models\Tahun_Tanam;

use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use FPDF;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KepemilikanController extends Controller
{
    /**
     * Tampilkan semua data kepemilikan.
     */
    public function index(Request $request)
    {
        $query = Kepemilikan::with([
            'petani.desa.kecamatan',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahunTanam'
        ]);

        // 🔍 Fitur pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('petani', function ($q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%")
                        ->orWhere('nomor_anggota_plasma', 'like', "%{$search}%")
                        ->orWhere('nomor_anggota_koperasi', 'like', "%{$search}%");
                })
                ->orWhereHas('detailKepemilikan.lahan.desa', function ($q2) use ($search) {
                    $q2->where('desa', 'like', "%{$search}%");
                })
                ->orWhereHas('detailKepemilikan.lahan.tahunTanam', function ($q2) use ($search) {
                    $q2->where('tahun', 'like', "%{$search}%");
                });
            });
        }

        // Gunakan paginate agar tetap bisa navigasi halaman
        $kepemilikan = $query->paginate(10);

        // 🎯 Filter hasil detail di setiap kepemilikan supaya hanya tampil yang cocok
        if (!empty($request->search)) {
            $search = strtolower($request->search);

            $kepemilikan->getCollection()->transform(function ($item) use ($search) {
                $item->detailKepemilikan = $item->detailKepemilikan->filter(function ($detail) use ($search) {
                    $desa  = strtolower($detail->lahan->desa->desa ?? '');
                    $tahun = strtolower($detail->lahan->tahunTanam->tahun ?? '');
                    return str_contains($desa, $search) || str_contains($tahun, $search);
                })->values();
                return $item;
            });

            // 🚫 Jika semua detail kosong setelah difilter, jangan tampilkan petani itu sama sekali
            $kepemilikan->setCollection(
                $kepemilikan->getCollection()->filter(function ($item) {
                    return $item->detailKepemilikan->isNotEmpty();
                })->values()
            );
        }

        return view('kepemilikan.index', compact('kepemilikan'));
    }

    public function showPerLahan($id_kepemilikan, $id_lahan)
    {
        $kepemilikan = Kepemilikan::with(['petani', 'detailKepemilikan.lahan.desa.kecamatan', 'detailKepemilikan.lahan.tahunTanam'])
            ->findOrFail($id_kepemilikan);

        $selectedDetail = $kepemilikan->detailKepemilikan->firstWhere('id_lahan', $id_lahan);

        if (!$selectedDetail) {
            abort(404, 'Lahan tidak ditemukan untuk kepemilikan ini.');
        }

        return view('kepemilikan.detail_per_lahan', [
            'kepemilikan' => $kepemilikan,
            'detail' => $selectedDetail, // ✅ biar Blade tetap pakai $detail
        ]);
    }


        public function editPerLahan($id_kepemilikan, $id_lahan)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahunTanam'
        ])->findOrFail($id_kepemilikan);

        $selectedDetail = $kepemilikan->detailKepemilikan->firstWhere('id_lahan', $id_lahan);

        if (!$selectedDetail) {
            abort(404, 'Lahan tidak ditemukan untuk kepemilikan ini.');
        }

        $petani = Petani::with('desa.kecamatan')->get();
        $desa = Desa::with('kecamatan')->get();
        $tahun_tanam = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        return view('kepemilikan.edit_per_lahan', compact('kepemilikan', 'selectedDetail', 'petani', 'desa', 'tahun_tanam'));
    }

        public function destroyPerLahan($id_kepemilikan, $id_lahan)
    {
        // Cek apakah kepemilikan dan lahan cocok
        $detail = \App\Models\DetailKepemilikan::where('id_kepemilikan', $id_kepemilikan)
            ->where('id_lahan', $id_lahan)
            ->first();

        if (!$detail) {
            return redirect()->back()->with('error', 'Data lahan tidak ditemukan untuk kepemilikan ini.');
        }

        // Hapus detail kepemilikan (hanya lahan itu)
        $detail->delete();

        return redirect()->back()->with('success', 'Data lahan berhasil dihapus dari kepemilikan.');
    }


    /**
     * Form tambah kepemilikan baru.
     */
    public function create()
    {
        $petani = Petani::with('desa.kecamatan')->get();
        $desa = Desa::with(relations: 'kecamatan')->get();
        $tahun_tanam = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        return view('kepemilikan.create', compact('petani', 'desa', 'tahun_tanam'));
    }

    /**
     * Simpan data kepemilikan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'status_kepemilikan' => 'required|in:aktif,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

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
        ]);

        DB::beginTransaction();

        try {
            $kepemilikan = Kepemilikan::create([
                'id_petani' => $request->id_petani,
                'status_kepemilikan' => $request->status_kepemilikan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            foreach ($request->lahan as $lahanData) {
                $lahan = Lahan::create([
                    'id_desa' => $lahanData['id_desa'],
                    'id_tahun_tanam' => $lahanData['id_tahun_tanam'],
                    'luas_peta' => $lahanData['luas_peta'],
                ]);

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
                ]);
            }

            DB::commit();

            return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Form edit kepemilikan.
     */
    public function edit($id)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan' => function ($q) use ($id) {
                $q->where('id_kepemilikan', $id)
                ->with(['lahan.desa.kecamatan', 'lahan.tahunTanam']);
            }
        ])->findOrFail($id);

        $petani = Petani::with('desa.kecamatan')->get();
        $desa = Desa::with('kecamatan')->get();
        $tahun_tanam = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        return view('kepemilikan.edit', compact('kepemilikan', 'petani', 'desa', 'tahun_tanam'));
    }

    /**
     * Update data kepemilikan.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'status_kepemilikan' => 'required|in:aktif,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

            'lahan' => 'required|array|min:1',
            'lahan.*.id_detail_kepemilikan' => 'nullable|exists:detail_kepemilikan,id_detail_kepemilikan',
            'lahan.*.id_lahan' => 'nullable|exists:lahan,id_lahan',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.luas_peta' => 'required|numeric|min:0',
            'lahan.*.pdf_scan_shm' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.pdf_scan_peta' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $kepemilikan = Kepemilikan::findOrFail($id);
            $kepemilikan->update([
                'id_petani' => $request->id_petani,
                'status_kepemilikan' => $request->status_kepemilikan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            foreach ($request->lahan as $index => $lahanData) {
                // cari detail kepemilikan lama
                $detail = DetailKepemilikan::find($lahanData['id_detail_kepemilikan'] ?? null);

                if ($detail) {
                    $lahan = Lahan::find($lahanData['id_lahan']);
                } else {
                    // jika data baru
                    $lahan = new Lahan();
                    $detail = new DetailKepemilikan();
                    $detail->id_kepemilikan = $kepemilikan->id_kepemilikan;
                }

                // update data lahan
                $lahan->id_desa = $lahanData['id_desa'];
                $lahan->id_tahun_tanam = $lahanData['id_tahun_tanam'];
                $lahan->luas_peta = $lahanData['luas_peta'];
                $lahan->save();

                // Handle file SHM
                if ($request->hasFile("lahan.$index.pdf_scan_shm")) {
                    $shmPath = $request->file("lahan.$index.pdf_scan_shm")->store('shm_pdf', 'public');
                } else {
                    $shmPath = $detail->pdf_scan_shm ?? null;
                }

                // Handle file Peta
                if ($request->hasFile("lahan.$index.pdf_scan_peta")) {
                    $petaPath = $request->file("lahan.$index.pdf_scan_peta")->store('peta_pdf', 'public');
                } else {
                    $petaPath = $detail->pdf_scan_peta ?? null;
                }

                // Simpan detail
                $detail->fill([
                    'id_lahan' => $lahan->id_lahan,
                    'nomor_SHM' => $lahanData['nomor_SHM'] ?? null,
                    'nama_SHM' => $lahanData['nama_SHM'] ?? null,
                    'nomor_sporadik' => $lahanData['nomor_sporadik'] ?? null,
                    'nama_sporadik' => $lahanData['nama_sporadik'] ?? null,
                    'nomor_kavling' => $lahanData['nomor_kavling'] ?? null,
                    'luas_surat' => $lahanData['luas_surat'] ?? null,
                    'nomor_pbb' => $lahanData['nomor_pbb'] ?? null,
                    'jumlah_pbb' => $lahanData['jumlah_pbb'] ?? null,
                    'pdf_scan_shm' => $shmPath,
                    'pdf_scan_peta' => $petaPath,
                ]);
                $detail->save();
            }

            DB::commit();

            return redirect()->route('kepemilikan.index')->with('success', 'Data kepemilikan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus kepemilikan.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $kepemilikan = Kepemilikan::findOrFail($id);
            $detail = DetailKepemilikan::where('id_kepemilikan', $id)->get();

            foreach ($detail as $d) {
                $d->lahan()->delete();
                $d->delete();
            }

            $kepemilikan->delete();

            DB::commit();

            return redirect()->route(route: 'kepemilikan.index')->with('success', 'Data kepemilikan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /*
     * function untuk menampilkan detail
     */
    public function show($id_kepemilikan)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan' => function ($q) use ($id_kepemilikan) {
                $q->where('id_kepemilikan', $id_kepemilikan)
                ->with(['lahan.desa.kecamatan', 'lahan.tahunTanam']);
            }
        ])->findOrFail($id_kepemilikan);

        return view('kepemilikan.detail', compact('kepemilikan'));
    }


    public function cetakPDF($id)
    {
        // 1️⃣ Ambil data kepemilikan
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahun_tanam'
        ])->findOrFail($id);

        // 2️⃣ Generate PDF utama dari view (DomPDF) dan simpan sementara
        $pdf = Pdf::loadView('kepemilikan.pdf', compact('kepemilikan'))
            ->setPaper('a4', 'portrait');

        $pathMain = storage_path('app/public/kepemilikan.pdf'); // simpan sementara
        $pdf->save($pathMain);

        // 3️⃣ Ambil semua lampiran PDF dari detail
        $lampiranFiles = [];
        if (
            $kepemilikan->petani->pdf_scan_ktp &&
            file_exists(storage_path('app/public/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp))
        ) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp);
        }

        // 4️⃣ Merge PDF utama + lampiran (pakai FPDI)
        $pdfMerger = new Fpdi();

        // Tambahkan halaman dari PDF utama
        $pageCount = $pdfMerger->setSourceFile($pathMain);
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdfMerger->AddPage();
            $tpl = $pdfMerger->importPage($i);
            $pdfMerger->useTemplate($tpl);
        }

        // Tambahkan halaman dari semua lampiran
        foreach ($lampiranFiles as $file) {
            $pageCount = $pdfMerger->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $pdfMerger->AddPage();
                $tpl = $pdfMerger->importPage($i);
                $pdfMerger->useTemplate($tpl);
            }
        }

        // 5️⃣ Simpan PDF gabungan & download
        $finalPath = storage_path('app/public/kepemilikan_gabungan.pdf');
        $pdfMerger->Output($finalPath, 'F');

        return response()->download($finalPath, 'Data_Kepemilikan_' . $kepemilikan->petani->nama . '.pdf');
    }

}
