<?php

namespace App\Http\Controllers;

use App\Models\Kepemilikan;
use App\Models\DetailKepemilikan;
use App\Models\Petani;
use App\Models\Lahan;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\Pbb;
use App\Models\RiwayatKepemilikan;
use Carbon\Carbon;

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

        $search = strtolower($request->search ?? '');

        //Pencarian gabungan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('petani', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(nama) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(nomor_anggota_plasma) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(nomor_anggota_koperasi) like ?', ["%{$search}%"]);
                })
                    ->orWhereHas('detailKepemilikan.lahan.desa', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(desa) like ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('detailKepemilikan.lahan.tahunTanam', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(tahun) like ?', ["%{$search}%"]);
                    });
            });
        }

        //Filter dropdown (desa dan tahun)
        if ($request->filled('desa')) {
            $query->whereHas('detailKepemilikan.lahan.desa', function ($q) use ($request) {
                $q->where('desa', $request->desa);
            });
        }

        if ($request->filled('tahun')) {
            $query->whereHas('detailKepemilikan.lahan.tahunTanam', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        $kepemilikan = $query->paginate(10)->appends($request->all());

        // Logika MERGE hasil search nama/nomor plasma
        if (!empty($search)) {
            $isSearchPetani = Kepemilikan::whereHas('petani', function ($q) use ($search) {
                $q->whereRaw('LOWER(nama) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_anggota_plasma) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_anggota_koperasi) like ?', ["%{$search}%"]);
            })->exists();

            // Kalau yang dicari nama atau nomor plasma → tampilkan SEMUA desa & tahun miliknya
            if ($isSearchPetani) {
                $kepemilikan->getCollection()->transform(function ($item) {
                    $item->detailKepemilikan = $item->detailKepemilikan->unique(function ($d) {
                        return ($d->lahan->desa->desa ?? '') . '-' . ($d->lahan->tahunTanam->tahun ?? '');
                    })->values();
                    return $item;
                });
            } else {
                // Tapi kalau search desa/tahun → tetap filter
                $kepemilikan->getCollection()->transform(function ($item) use ($search) {
                    $item->detailKepemilikan = $item->detailKepemilikan->filter(function ($detail) use ($search) {
                        $desa = strtolower($detail->lahan->desa->desa ?? '');
                        $tahun = strtolower($detail->lahan->tahunTanam->tahun ?? '');
                        return str_contains($desa, $search) || str_contains($tahun, $search);
                    })->values();
                    return $item;
                });
            }
        }

        // Filter ulang data berdasarkan dropdown (desa & tahun)
        $kepemilikan->getCollection()->transform(function ($item) use ($request) {
            $item->detailKepemilikan = $item->detailKepemilikan->filter(function ($detail) use ($request) {
                $byDesa = !$request->filled('desa') || ($detail->lahan->desa->desa ?? '') === $request->desa;
                $byTahun = !$request->filled('tahun') || ($detail->lahan->tahunTanam->tahun ?? '') == $request->tahun;
                return $byDesa && $byTahun;
            })->values();
            return $item;
        });

        // Hapus data tanpa detail
        $kepemilikan->setCollection(
            $kepemilikan->getCollection()->filter(function ($item) {
                return $item->detailKepemilikan->isNotEmpty();
            })->values()
        );

        // Dropdown
        $daftarDesa = Desa::orderBy('desa')->get();
        $daftarTahun = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        // Tentukan mode tampilan otomatis
        if ($request->filled('desa') || $request->filled('tahun')) {
            $mode = 'perLahan'; // kalau filter aktif
        } elseif (!empty($search)) {
            $isSearchPetani = Kepemilikan::whereHas('petani', function ($q) use ($search) {
                $q->whereRaw('LOWER(nama) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_anggota_plasma) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_anggota_koperasi) like ?', ["%{$search}%"]);
            })->exists();

            // kalau search nama petani → tetap normal, kalau bukan → per lahan
            $mode = $isSearchPetani ? 'normal' : 'perLahan';
        } else {
            $mode = 'normal'; // default
        }

        return view('kepemilikan.index', compact('kepemilikan', 'daftarDesa', 'daftarTahun', 'mode'));
    }



    public function showPerLahan($id_kepemilikan, $id_lahan)
    {
        $kepemilikan = Kepemilikan::with(['petani', 'detailKepemilikan.lahan.desa.kecamatan', 'detailKepemilikan.lahan.tahunTanam'])
            ->findOrFail($id_kepemilikan);

        $selectedDetail = $kepemilikan->detailKepemilikan->firstWhere('id_lahan', $id_lahan);

        if (!$selectedDetail) {
            abort(404, 'Lahan tidak ditemukan untuk kepemilikan ini.');
        }

        // 🧩 Tambahan: Auto generate PBB tahun depan (hanya bulan Oktober)
        $bulanSekarang = Carbon::now()->month;
        $tahunSekarang = Carbon::now()->year;
        $tahunDepan = $tahunSekarang + 1;

        if ($bulanSekarang == 10) {
            $jumlahPBB = $selectedDetail->jumlah_pbb ?? 0;
            $pbbTahunIni = $selectedDetail->pbb->where('tahun', $tahunSekarang)->first();

            // Kalau tahun ini belum lunas, tambahkan ke akumulasi
            if ($pbbTahunIni && $pbbTahunIni->status == 'belum') {
                $jumlahPBB += $pbbTahunIni->jumlah;
            }

            // Cek apakah tahun depan sudah ada
            $pbbTahunDepan = $selectedDetail->pbb->where('tahun', $tahunDepan)->first();

            if ($pbbTahunDepan) {
                // Kalau sudah ada, update ulang jumlahnya
                $pbbTahunDepan->update([
                    'jumlah' => $jumlahPBB,
                    'status' => 'belum',
                ]);
            } else {
                // Kalau belum ada, buat baru
                Pbb::create([
                    'id_detail_kepemilikan' => $selectedDetail->id_detail_kepemilikan,
                    'tahun' => $tahunDepan,
                    'jumlah' => $jumlahPBB,
                    'status' => 'belum',
                ]);
            }
        }

        return view('kepemilikan.detail_per_lahan', [
            'kepemilikan' => $kepemilikan,
            'detail' => $selectedDetail,
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

    public function updatePerLahan(Request $request, $id_kepemilikan, $id_lahan)
    {
        $data = $request->input('lahan')[0]; // ambil data lahan pertama (karena cuma satu)

        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'lahan.0.id_desa' => 'required|exists:desa,id_desa',
            'lahan.0.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
        ]);

        $detail = DetailKepemilikan::where('id_kepemilikan', $id_kepemilikan)
            ->where('id_lahan', $id_lahan)
            ->firstOrFail();

        $kepemilikan = $detail->kepemilikan;
        $kepemilikan->update(['id_petani' => $request->id_petani]);

        $lahan = $detail->lahan;
        $lahan->update([
            'id_desa' => $data['id_desa'],
            'id_tahun_tanam' => $data['id_tahun_tanam'],
            'luas_peta' => $data['luas_peta'],
        ]);

        $detail->update([
            'nomor_SHM' => $data['nomor_SHM'],
            'nama_SHM' => $data['nama_SHM'],
            'nomor_kavling' => $data['nomor_kavling'],
            'nomor_sporadik' => $data['nomor_sporadik'],
            'nama_sporadik' => $data['nama_sporadik'],
            'nomor_pbb' => $data['nomor_pbb'],
            'luas_surat' => $data['luas_surat'],
            'jumlah_pbb' => $data['jumlah_pbb'],
            'status_kepemilikan' => $data['status_kepemilikan'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
        ]);

        return redirect()->route('kepemilikan.index', $id_kepemilikan)
            ->with('success', 'Data lahan dan kepemilikan berhasil diperbarui.');
    }


    public function destroyPerLahan($id_kepemilikan, $id_lahan)
    {
        // Cek apakah kepemilikan dan lahan cocok
        $detail = DetailKepemilikan::where('id_kepemilikan', $id_kepemilikan)
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

            'lahan' => 'required',
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
        $lahan = $kepemilikan->detailKepemilikan->pluck('lahan');


        return view('kepemilikan.edit', compact('kepemilikan', 'petani', 'desa', 'tahun_tanam', 'lahan'));
    }

    /**
     * Update data kepemilikan.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',

            'lahan' => 'required',
            'lahan.*.id_detail_kepemilikan' => 'nullable|exists:detail_kepemilikan,id_detail_kepemilikan',
            'lahan.*.id_lahan' => 'nullable|exists:lahan,id_lahan',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.luas_peta' => 'required|numeric|min:0',
            'lahan.*.pdf_scan_shm' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.pdf_scan_peta' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.status_kepemilikan' => 'required|in:aktif,nonaktif',
            'lahan.*.tanggal_mulai' => 'nullable|date',
            'lahan.*.tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();

        try {
            $kepemilikan = Kepemilikan::findOrFail($id);
            $kepemilikan->update([
                'id_petani' => $request->id_petani,
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
                // File SHM
                if ($request->hasFile("lahan.$index.pdf_scan_shm")) {
                    $shmPath = $request->file("lahan.$index.pdf_scan_shm")->store('shm_pdf', 'public');
                } else {
                    $shmPath = $detail->pdf_scan_shm ?? null;
                }

                // File Peta
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
                    'status_kepemilikan' => $lahanData['status_kepemilikan'],
                    'tanggal_mulai' => $lahanData['tanggal_mulai'],
                    'tanggal_selesai' => $lahanData['tanggal_selesai'],
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
                    ->with(['lahan.desa.kecamatan', 'lahan.tahunTanam', 'pbb']);
            }
        ])->findOrFail($id_kepemilikan);

        return view('kepemilikan.detail', compact('kepemilikan'));
    }

    public function cetakPDF($id)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahunTanam'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('kepemilikan.pdf', compact('kepemilikan'))
            ->setPaper('a4', 'portrait');

        $pathMain = storage_path('app/public/kepemilikan.pdf');
        $pdf->save($pathMain);

        $lampiranFiles = [];

        // Tambahkan KTP & KK
        if ($kepemilikan->petani->pdf_scan_ktp && file_exists(storage_path('app/public/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp))) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp);
        }
        if ($kepemilikan->petani->pdf_scan_kk && file_exists(storage_path('app/public/ktp_pdf/' . $kepemilikan->petani->pdf_scan_kk))) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $kepemilikan->petani->pdf_scan_kk);
        }

        // Tambahkan SHM & PETA
        foreach ($kepemilikan->detailKepemilikan as $detail) {
            // hapus duplikasi path
            $pathShm = storage_path('app/public/' . $detail->pdf_scan_shm);
            $pathPeta = storage_path('app/public/' . $detail->pdf_scan_peta);

            if ($detail->pdf_scan_shm && file_exists(filename: $pathShm)) {
                $lampiranFiles[] = $pathShm;
            }

            if ($detail->pdf_scan_peta && file_exists($pathPeta)) {
                $lampiranFiles[] = $pathPeta;
            }
        }

        // Merge semua PDF
        $pdfMerger = new Fpdi();

        // Tambah file utama
        $pageCount = $pdfMerger->setSourceFile($pathMain);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdfMerger->importPage($i);
            $size = $pdfMerger->getTemplateSize($tpl);
            $pdfMerger->AddPage('P', [$size['width'], $size['height']]);
            $pdfMerger->useTemplate($tpl);
        }

        // Tambah semua lampiran (otomatis detect orientasi)
        foreach ($lampiranFiles as $file) {
            $pageCount = $pdfMerger->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdfMerger->importPage($i);
                $size = $pdfMerger->getTemplateSize($tpl);

                if ($size['width'] > $size['height']) {
                    $pdfMerger->AddPage('L', [$size['width'], $size['height']]);
                } else {
                    $pdfMerger->AddPage('P', [$size['width'], $size['height']]);
                }

                $pdfMerger->useTemplate($tpl);
            }
        }

        $finalPath = storage_path('app/public/kepemilikan_gabungan.pdf');
        $pdfMerger->Output($finalPath, 'F');

        return response()->download($finalPath, 'Data Kepemilikan Lahan ' . $kepemilikan->petani->nama . '.pdf');
    }

    public function cetakPDFPerLahan($id_kepemilikan, $id_detail)
    {
        $detail = DetailKepemilikan::with(['lahan.desa.kecamatan', 'lahan.tahunTanam', 'kepemilikan.petani'])
            ->where('id_kepemilikan', $id_kepemilikan)
            ->where('id_detail_kepemilikan', $id_detail)
            ->firstOrFail();

        $kepemilikan = $detail->kepemilikan;
        $petani = $kepemilikan->petani;

        // Generate PDF utama hanya untuk 1 detail
        $pdf = Pdf::loadView('kepemilikan.pdf_per_lahan', compact('kepemilikan', 'detail'))
            ->setPaper('a4', 'portrait');

        $pathMain = storage_path('app/public/kepemilikan_per_lahan.pdf');
        $pdf->save($pathMain);

        $lampiranFiles = [];

        // KTP & KK Petani
        if ($petani->pdf_scan_ktp && file_exists(storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_ktp))) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_ktp);
        }
        if ($petani->pdf_scan_kk && file_exists(storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_kk))) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_kk);
        }

        // SHM & PETA hanya untuk lahan ini
        if ($detail->pdf_scan_shm && file_exists(storage_path('app/public/' . $detail->pdf_scan_shm))) {
            $lampiranFiles[] = storage_path('app/public/' . $detail->pdf_scan_shm);
        }

        if ($detail->pdf_scan_peta && file_exists(storage_path('app/public/' . $detail->pdf_scan_peta))) {
            $lampiranFiles[] = storage_path('app/public/' . $detail->pdf_scan_peta);
        }

        // Gabungkan PDF
        $pdfMerger = new Fpdi();
        $pageCount = $pdfMerger->setSourceFile($pathMain);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdfMerger->importPage($i);
            $size = $pdfMerger->getTemplateSize($tpl);
            $pdfMerger->AddPage('P', [$size['width'], $size['height']]);
            $pdfMerger->useTemplate($tpl);
        }

        foreach ($lampiranFiles as $file) {
            $pageCount = $pdfMerger->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdfMerger->importPage($i);
                $size = $pdfMerger->getTemplateSize($tpl);

                $pdfMerger->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                $pdfMerger->useTemplate($tpl);
            }
        }

        $finalPath = storage_path('app/public/kepemilikan_per_lahan_gabungan.pdf');
        $pdfMerger->Output($finalPath, 'F');

        return response()->download(
            $finalPath,
            'Kepemilikan '
            . ($petani->nama ?? 'Tanpa Nama')
            . ' - '
            . ($detail->lahan->desa->desa ?? 'Lahan')
            . ' ('
            . ($detail->lahan->tahunTanam->tahun ?? 'Tahun Tidak Diketahui')
            . ').pdf'
        );
    }

    public function cetakSemuaPDF(Request $request)
    {
        $query = Kepemilikan::with([
            'petani.desa.kecamatan',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahunTanam'
        ]);

        //Gabungkan semua filter utama
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sub) use ($search) {
                $sub->whereHas('petani', function ($q2) use ($search) {
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
        });

        // Filter tambahan berdasarkan dropdown (desa & tahun)
        $query->when($request->filled('desa'), function ($q) use ($request) {
            $q->whereHas('detailKepemilikan.lahan.desa', function ($q2) use ($request) {
                $q2->where('desa', $request->desa);
            });
        });

        $query->when($request->filled('tahun'), function ($q) use ($request) {
            $q->whereHas('detailKepemilikan.lahan.tahunTanam', function ($q2) use ($request) {
                $q2->where('tahun', $request->tahun);
            });
        });

        //Ambil hasil akhir
        $kepemilikan = $query->get();

        if ($kepemilikan->isEmpty()) {
            return back()->with('error', 'Tidak ada data yang cocok dengan filter atau pencarian.');
        }

        // Filter ulang detail agar sesuai juga
        $kepemilikan->each(function ($kep) use ($request) {
            $kep->detailKepemilikan = $kep->detailKepemilikan->filter(function ($detail) use ($request) {
                $matchDesa = !$request->filled('desa') ||
                    ($detail->lahan && $detail->lahan->desa->desa == $request->desa);
                $matchTahun = !$request->filled('tahun') ||
                    ($detail->lahan && $detail->lahan->tahunTanam->tahun == $request->tahun);

                if ($request->filled('search')) {
                    $s = strtolower($request->search);
                    $matchSearch =
                        str_contains(strtolower($detail->kepemilikan->petani->nama ?? ''), $s) ||
                        str_contains(strtolower($detail->kepemilikan->petani->nomor_anggota_plasma ?? ''), $s) ||
                        str_contains(strtolower($detail->lahan->desa->desa ?? ''), $s) ||
                        str_contains(strtolower($detail->lahan->tahunTanam->tahun ?? ''), $s);
                } else {
                    $matchSearch = true;
                }

                return $matchDesa && $matchTahun && $matchSearch;
            });
        });

        //Hapus kepemilikan yang detail-nya kosong
        $kepemilikan = $kepemilikan->filter(fn($kep) => $kep->detailKepemilikan->isNotEmpty());

        if ($kepemilikan->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan setelah filter diterapkan.');
        }

        //Buat PDF
        $pdf = Pdf::loadView('kepemilikan.pdf_data', [
            'kepemilikan' => $kepemilikan,
            'request' => $request
        ])->setPaper('a4', 'landscape');

        $namaDesa = $request->filled('desa') ? str_replace(' ', '_', $request->desa) : 'SemuaDesa';
        $namaTahun = $request->filled('tahun') ? $request->tahun : 'SemuaTahun';

        $namaFile = "Data_Kepemilikan_{$namaDesa}_{$namaTahun}.pdf";

        return $pdf->download($namaFile);
    }

    // data PBB per lahan //
    //Menandai PBB tahun tertentu sebagai lunas
    public function tandaiLunasPbb($id_pbb)
    {
        $pbb = Pbb::findOrFail($id_pbb);
        $pbb->update(['status' => 'lunas']);

        // Reload relasi dan data terbaru
        $pbb->refresh();
        $pbb->detailKepemilikan->load('pbb');

        return redirect()->route('kepemilikan.show', $pbb->detailKepemilikan->id_kepemilikan)
            ->with('success', 'PBB tahun ' . $pbb->tahun . ' telah ditandai lunas.');
    }

    //Membuat akumulasi otomatis untuk tahun berikutnya
    public function generatePbbTahunBaru($id_detail_kepemilikan)
    {
        $tahunSekarang = Carbon::now()->year;
        $bulanSekarang = Carbon::now()->month;

        //Pastikan hanya bisa dijalankan di bulan Oktober
        if ($bulanSekarang != 10) {
            return back()->with('error', 'Akumulasi PBB hanya dapat dilakukan pada bulan Oktober.');
        }

        $detail = DetailKepemilikan::with('pbb')->findOrFail($id_detail_kepemilikan);
        $jumlahPBB = $detail->jumlah_pbb ?? 0;

        $pbbTahunIni = $detail->pbb->where('tahun', $tahunSekarang)->first();
        $tahunDepan = $tahunSekarang + 1;

        //Kalau tahun ini belum lunas → tambahkan ke akumulasi
        if ($pbbTahunIni && $pbbTahunIni->status == 'belum') {
            $jumlahPBB += $pbbTahunIni->jumlah;
        }

        //Cek apakah tahun depan sudah ada data
        $pbbTahunDepan = $detail->pbb->where('tahun', $tahunDepan)->first();

        if ($pbbTahunDepan) {
            //Kalau sudah ada → update ulang jumlah sesuai data terbaru
            $pbbTahunDepan->update([
                'jumlah' => $jumlahPBB,
                'status' => 'belum', // pastikan tetap belum sampai dibayar
            ]);
            $message = 'Data PBB tahun ' . $tahunDepan . ' telah diperbarui sesuai jumlah terbaru.';
        } else {
            //Kalau belum ada → buat baru
            Pbb::create([
                'id_detail_kepemilikan' => $detail->id_detail_kepemilikan,
                'tahun' => $tahunDepan,
                'jumlah' => $jumlahPBB,
                'status' => 'belum',
            ]);
            $message = 'PBB tahun ' . $tahunDepan . ' berhasil dibuat!';
        }

        return back()->with('success', $message);
    }

    public function gantiKepemilikan(Request $request, $id_lahan)
    {
        $request->validate([
            'nomor_anggota_plasma' => 'required|string',
            'nomor_anggota_koperasi' => 'required|string',
            'NIK' => 'required|string|max:16',
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'status' => 'required|in:aktif,tidak_aktif',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/', // validasi angka & +62
            'pdf_scan_ktp' => 'nullable|mimes:pdf|max:10240',
            'pdf_scan_kk' => 'nullable|mimes:pdf|max:10240',
        ]);

<<<<<<< HEAD
=======
        // Format nomor telepon
        $no_telepon = $request->no_telepon;
        if ($no_telepon) {
            if (substr($no_telepon, 0, 1) === '0') {
                $no_telepon = '+62' . substr($no_telepon, 1);
            }
        }

>>>>>>> ad321bea1af9ccecaac0c0df668c5f7b9241105c
        // Simpan file PDF (jika ada)
        $ktpName = $request->hasFile('pdf_scan_ktp')
            ? time() . '_' . $request->file('pdf_scan_ktp')->getClientOriginalName()
            : null;

        $kkName = $request->hasFile('pdf_scan_kk')
            ? time() . '_' . $request->file('pdf_scan_kk')->getClientOriginalName()
            : null;

        if ($ktpName) {
            $request->file('pdf_scan_ktp')->storeAs('ktp_pdf', $ktpName, 'public');
        }
        if ($kkName) {
            $request->file('pdf_scan_kk')->storeAs('ktp_pdf', $kkName, 'public');
        }

        // Buat petani baru
        $petaniBaru = Petani::create([
            'nomor_anggota_plasma' => $request->nomor_anggota_plasma,
            'nomor_anggota_koperasi' => $request->nomor_anggota_koperasi,
            'NIK' => $request->NIK,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'no_telepon' => $no_telepon,
            'pdf_scan_ktp' => $ktpName,
            'pdf_scan_kk' => $kkName,
        ]);

        // Ambil data lahan lama
        $lahan = DetailKepemilikan::findOrFail($id_lahan);
        $id_petani_lama = $lahan->id_petani;

        // Catat riwayat kepemilikan
        RiwayatKepemilikan::create([
            'id_lahan' => $id_lahan,
            'id_petani_sebelum' => $id_petani_lama,
            'id_petani_sesudah' => $petaniBaru->id_petani,
            'tanggal_ganti' => now(),
            'keterangan' => 'Ganti kepemilikan dari ' . $lahan->petani->nama . ' ke ' . $petaniBaru->nama,
        ]);

        // Update lahan ke petani baru
        $lahan->update(['id_petani' => $petaniBaru->id_petani]);

        return redirect()->back()->with('success', 'Kepemilikan berhasil diganti dan petani baru ditambahkan.');
    }

    public function riwayatLahan($id_lahan)
    {
        $riwayat = RiwayatKepemilikan::with([
            'petaniSebelum',
            'petaniSesudah',
            'lahan.desa.kecamatan'
        ])
        ->where('id_lahan', $id_lahan)
        ->orderByDesc('tanggal_ganti')
        ->get();

        $lahan = Lahan::with('desa.kecamatan')->findOrFail($id_lahan);

        return view('kepemilikan.riwayat_lahan', compact('riwayat', 'lahan'));
    }

    public function updateKepemilikan(Request $request, $id_kepemilikan, $id_lahan)
    {
        $kepemilikan = Kepemilikan::with('detailKepemilikan')->findOrFail($id_kepemilikan);
        $detail = $kepemilikan->detailKepemilikan->firstWhere('id_lahan', $id_lahan);

        if (!$detail) {
            abort(404, 'Lahan tidak ditemukan.');
        }

        $validated = $request->validate([
            'mode' => 'required|in:lama,baru',
            'id_petani_lama' => 'required|exists:petani,id_petani',
            'id_petani_baru' => 'nullable|required_if:mode,lama|exists:petani,id_petani',
            'nama' => 'nullable|string|max:255',
            'NIK' => 'nullable|string|max:16',
            'nomor_anggota_plasma' => 'nullable|string|max:100',
            'nomor_anggota_koperasi' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/', // validasi nomor telepon
            'tanggal_ganti' => 'required|date',
            'pdf_scan_ktp' => 'nullable|mimes:pdf|max:10240',
            'pdf_scan_kk' => 'nullable|mimes:pdf|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        // Ambil id petani dari kepemilikan lama
        $id_petani_sebelum = $kepemilikan->id_petani;

        if ($validated['mode'] === 'lama') {
            $id_petani_sesudah = $validated['id_petani_baru'];
        } else {
            // Format nomor telepon
            $no_telepon = $validated['no_telepon'] ?? null;
            if ($no_telepon && substr($no_telepon, 0, 1) === '0') {
                $no_telepon = '+62' . substr($no_telepon, 1);
            }

            $petaniBaru = Petani::create([
                'nomor_anggota_plasma' => $validated['nomor_anggota_plasma'],
                'nomor_anggota_koperasi' => $validated['nomor_anggota_koperasi'],
                'NIK' => $validated['NIK'],
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'status' => $validated['status'] ?? 'aktif',
                'no_telepon' => $no_telepon,
            ]);

            if ($request->hasFile('pdf_scan_ktp')) {
                $petaniBaru->pdf_scan_ktp = $request->file('pdf_scan_ktp')->store('ktp_pdf', 'public');
            }
            if ($request->hasFile('pdf_scan_kk')) {
                $petaniBaru->pdf_scan_kk = $request->file('pdf_scan_kk')->store('kk_pdf', 'public');
            }
            $petaniBaru->save();

            $id_petani_sesudah = $petaniBaru->id_petani;
        }

        // Cari atau buat kepemilikan untuk petani baru
        $kepemilikanBaru = Kepemilikan::firstOrCreate(
            ['id_petani' => $id_petani_sesudah],
            ['tanggal_kepemilikan' => now(), 'status' => 'aktif']
        );

        // Pindahkan lahan ke petani baru
        $detail->update([
            'id_kepemilikan' => $kepemilikanBaru->id_kepemilikan,
        ]);

        // Simpan riwayat perpindahan
        RiwayatKepemilikan::create([
            'id_lahan' => $id_lahan,
            'id_petani_sebelum' => $id_petani_sebelum,
            'id_petani_sesudah' => $id_petani_sesudah,
            'tanggal_ganti' => $validated['tanggal_ganti'],
            'keterangan' => $validated['keterangan'] ?? 'Perubahan kepemilikan lahan',
        ]);

        return redirect()->route('kepemilikan.editPerLahan', [$kepemilikanBaru->id_kepemilikan, $id_lahan])
            ->with('success', 'Kepemilikan lahan berhasil dipindahkan.');
    }

}
