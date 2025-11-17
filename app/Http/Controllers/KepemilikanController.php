<?php

namespace App\Http\Controllers;

//Models
use App\Models\Kepemilikan;
use App\Models\DetailKepemilikan;
use App\Models\Petani;
use App\Models\Lahan;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use Illuminate\Support\Facades\Storage;
use App\Models\Pbb;
use App\Models\RiwayatKepemilikan;

//Tanggal//
use Carbon\Carbon;

//PDF//
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
        $query = Kepemilikan::select('kepemilikan.*')
            ->leftJoin('petani', 'kepemilikan.id_petani', '=', 'petani.id_petani')
            ->with([
                'petani.desa.kecamatan',
                'detailKepemilikan.lahan.desa.kecamatan',
                'detailKepemilikan.lahan.tahunTanam'
            ])
            ->orderBy('petani.nomor_anggota_plasma', 'asc');

        $statusPetaniRequest = strtolower($request->status_petani ?? '');
        $statusPengelolaanRequest = strtolower($request->status_pengelolaan ?? '');
        $search = strtolower($request->search ?? '');

        // ===================== FILTER STATUS PETANI =====================
        if ($statusPetaniRequest === 'berhenti') {
            $query->whereHas('petani', function ($q) {
                $q->where('status', 'berhenti');
            });
            // Jangan paksa whereHas detailKepemilikan di sini
        } else {
            // Default petani aktif
            $query->whereHas('petani', function ($q) {
                $q->where('status', 'aktif');
            });
        }

        $search = strtolower($request->search ?? '');
        $statusPengelolaan = strtolower($request->status_pengelolaan ?? '');

        // ===================== PENCARIAN GABUNGAN =====================
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
                    })
                    ->orWhereHas('detailKepemilikan', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(kode_lahan) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(status_pengelolaan) like ?', ["%{$search}%"]);
                    });
            });
        }

        // ===================== FILTER STATUS PENGELOLAAN =====================
        if (!empty($request->status_pengelolaan) && strtolower($request->status_pengelolaan) !== 'semua') {
            $query->whereHas('detailKepemilikan', function ($q) use ($request) {
                $q->whereRaw('LOWER(status_pengelolaan) = ?', [strtolower($request->status_pengelolaan)]);
            });
        }

        // ===================== FILTER DESA =====================
        if (!empty($request->desa) && strtolower($request->desa) !== 'semua') {
            $query->whereHas('detailKepemilikan.lahan.desa', function ($q) use ($request) {
                $q->where('desa', $request->desa);
            });
        }

        // ===================== FILTER TAHUN TANAM =====================
        if (!empty($request->tahun) && strtolower($request->tahun) !== 'semua') {
            $query->whereHas('detailKepemilikan.lahan.tahunTanam', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        // ===================== AMBIL DATA =====================
        $kepemilikan = $query
            ->whereHas('detailKepemilikan')
            ->paginate(10)
            ->appends($request->all());

        // pastikan detail unik per kepemilikan
        $kepemilikan->getCollection()->transform(function ($item) {
            $item->detailKepemilikan = $item->detailKepemilikan->unique('id_lahan')->values();
            return $item;
        });

        // pagination
        $kepemilikan->onEachSide(1);

        // Logika MERGE hasil search nama/nomor plasma
        if (!empty($search)) {
            $kepemilikan->getCollection()->transform(function ($item) use ($search) {
                $item->detailKepemilikan = $item->detailKepemilikan->filter(function ($detail) use ($search) {
                    $desa = strtolower($detail->lahan->desa->desa ?? '');
                    $tahun = strtolower($detail->lahan->tahunTanam->tahun ?? '');
                    $kodeLahan = strtolower($detail->kode_lahan ?? '');
                    $namaPetani = strtolower($detail->kepemilikan->petani->nama ?? '');
                    $nomorPlasma = strtolower($detail->kepemilikan->petani->nomor_anggota_plasma ?? '');
                    $nomorKoperasi = strtolower($detail->kepemilikan->petani->nomor_anggota_koperasi ?? '');
                    $statusPengelolaan = strtolower($detail->status_pengelolaan ?? '');

                    return str_contains($desa, $search)
                        || str_contains($tahun, $search)
                        || str_contains($kodeLahan, $search)
                        || str_contains($namaPetani, $search)
                        || str_contains($nomorPlasma, $search)
                        || str_contains($nomorKoperasi, $search)
                        || str_contains($statusPengelolaan, $search);
                })->values();
                return $item;
            });

        }

        $kepemilikan->getCollection()->transform(function ($item) use ($request, $search) {
            $item->detailKepemilikan = $item->detailKepemilikan->filter(function ($detail) use ($request, $item, $search) {
                $statusPetani = strtolower($item->petani->status ?? '');
                $statusKepemilikan = strtolower($detail->status_kepemilikan ?? '');
                $statusPengelolaan = strtolower($detail->status_pengelolaan ?? '');
                $filterPengelolaan = strtolower($request->status_pengelolaan ?? '');
                $filterPetani = strtolower($request->status_petani ?? '');
                $byDesa = empty($request->desa) || strtolower($request->desa) === 'semua' || strtolower($detail->lahan->desa->desa ?? '') === strtolower($request->desa);
                $byTahun = empty($request->tahun) || strtolower($request->tahun) === 'semua' || ($detail->lahan->tahunTanam->tahun ?? '') == $request->tahun;
                $isSearch = !empty($search);

                // ===================== PETANI BERHENTI =====================
                if ($statusPetani === 'berhenti') {
                    if ($filterPetani !== 'berhenti') return false;

                    if (!empty($filterPengelolaan) && $filterPengelolaan !== 'semua') {
                        return $statusPengelolaan === $filterPengelolaan && $byDesa && $byTahun;
                    }

                    // tampil semua lahan berhenti (Mandiri/Perusahaan)
                    return $byDesa && $byTahun;
                }

                // ===================== PETANI AKTIF =====================
                if ($statusPetani === 'aktif') {
                    // Jika ada filter pengelolaan → tampil sesuai filter
                    if (!empty($filterPengelolaan) && $filterPengelolaan !== 'semua') {
                        return $statusPengelolaan === $filterPengelolaan && $byDesa && $byTahun;
                    }

                    // Perusahaan nonaktif → tampil hanya kalau search atau filter pengelolaan
                    if ($statusKepemilikan === 'nonaktif' && $statusPengelolaan === 'perusahaan') {
                        if ($isSearch || (!empty($filterPengelolaan) && strtolower($filterPengelolaan) === 'perusahaan')) {
                            return $byDesa && $byTahun;
                        }
                        return false; // index default → tidak tampil
                    }

                    // Lahan aktif Mandiri → tampil selalu
                    if ($statusKepemilikan === 'aktif') {
                        return $byDesa && $byTahun;
                    }

                    return false; // nonaktif Mandiri → tidak tampil
                }


                return false;
            })->values();

            return $item;
        });

        // hapus data tanpa detail
        $kepemilikan->setCollection(
            $kepemilikan->getCollection()->filter(function ($item) {
                return $item->detailKepemilikan->isNotEmpty();
            })->values()
        );

        // dropdown data
        $daftarDesa = Desa::orderBy('desa')->get();
        $daftarTahun = Tahun_Tanam::orderBy('tahun', 'desc')->get();

        // mode tampilan
        if (
            (!empty($request->desa) && strtolower($request->desa) !== 'semua') ||
            (!empty($request->tahun) && strtolower($request->tahun) !== 'semua')
        ) {
            $mode = 'perLahan';
        } elseif (!empty($search)) {
            $isSearchPetani = Kepemilikan::whereHas('petani', function ($q) use ($search) {
                $q->whereRaw('LOWER(nama) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_anggota_plasma) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_anggota_koperasi) like ?', ["%{$search}%"]);
            })->exists();
            $mode = $isSearchPetani ? 'normal' : 'perLahan';
        } else {
            $mode = 'normal';
        }

        return view('kepemilikan.index', [
            'kepemilikan' => $kepemilikan,
            'daftarDesa' => $daftarDesa,
            'daftarTahun' => $daftarTahun,
            'mode' => $mode,
            'search' => $request->search,
            'desa' => $request->desa,
            'tahun' => $request->tahun,
            'status_pengelolaan' => $request->status_pengelolaan,
        ]);
    }

    public function showPerLahan(Request $request, $id_kepemilikan, $id_lahan)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan.lahan.desa.kecamatan',
            'detailKepemilikan.lahan.tahunTanam',
            'detailKepemilikan.pbb',
        ])->findOrFail($id_kepemilikan);

        $page = $request->page ?? 1;
        $search = $request->search ?? null;
        $filterDesa = $request->desa ?? null;
        $filterTahun = $request->tahun ?? null;

        // Ambil detail kepemilikan yang sesuai dengan lahan yang diklik
        $selectedDetail = $kepemilikan->detailKepemilikan->firstWhere('id_lahan', $id_lahan);

        if (!$selectedDetail) {
            abort(404, 'Lahan tidak ditemukan untuk kepemilikan ini.');
        }

        // Tentukan desa & tahun tanam dari lahan yang diklik
        $desaTarget = $selectedDetail->lahan->desa->desa ?? null;
        $tahunTarget = $selectedDetail->lahan->tahunTanam->tahun ?? null;

        // Ambil semua detail kepemilikan di desa & tahun tanam yang sama
        $groupDetails = DetailKepemilikan::with([
            'lahan.desa.kecamatan',
            'lahan.tahunTanam',
            'pbb',
        ])
            ->where('id_kepemilikan', $id_kepemilikan)
            ->whereHas('lahan', function ($q) use ($desaTarget, $tahunTarget) {
                $q->whereHas('desa', function ($qq) use ($desaTarget) {
                    $qq->where('desa', $desaTarget);
                })->whereHas('tahunTanam', function ($qq) use ($tahunTarget) {
                    $qq->where('tahun', $tahunTarget);
                });
            })
            ->get();

        //Hapus duplikat jika ada (berdasarkan ID detail)
        $groupDetails = $groupDetails->unique('id_detail_kepemilikan')->values();

        // Pastikan data PBB tahun berjalan tersedia
        $tahunSekarang = Carbon::now()->year;

        foreach ($groupDetails as $detail) {
            $pbbTahunIni = $detail->pbb->firstWhere('tahun', $tahunSekarang);

            if (!$pbbTahunIni) {
                Pbb::create([
                    'id_detail_kepemilikan' => $detail->id_detail_kepemilikan,
                    'tahun' => $tahunSekarang,
                    'jumlah' => $detail->jumlah_pbb ?? 0,
                    'status' => 'belum',
                ]);
            } else {
                // Kalau sudah ada dan statusnya belum selesai, update jumlahnya
                if ($pbbTahunIni->status === 'belum' && $pbbTahunIni->jumlah != $detail->jumlah_pbb) {
                    $pbbTahunIni->update([
                        'jumlah' => $detail->jumlah_pbb ?? 0,
                    ]);
                }
            }
        }

        // Refresh relasi PBB setelah mungkin ada yang baru dibuat
        $groupDetails->load('pbb');

        return view('kepemilikan.detail_per_lahan', [
            'kepemilikan' => $kepemilikan,
            'selectedDetail' => $selectedDetail,
            'groupDetails' => $groupDetails,
            'desaTarget' => $desaTarget,
            'tahunTarget' => $tahunTarget,
            'page' => $request->page,
            'search' => $request->search,
            'filterDesa' => $request->desa,
            'filterTahun' => $request->tahun,

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

        // Ambil desa & tahun tanam dari lahan yang diklik
        $desaTarget = $selectedDetail->lahan->desa->desa ?? null;
        $tahunTarget = $selectedDetail->lahan->tahunTanam->tahun ?? null;

        // Ambil semua detail yang satu desa dan satu tahun tanam
        $groupDetails = $kepemilikan->detailKepemilikan->filter(function ($detail) use ($desaTarget, $tahunTarget) {
            return ($detail->lahan->desa->desa ?? '') === $desaTarget &&
                ($detail->lahan->tahunTanam->tahun ?? '') === $tahunTarget;
        })->unique('id_lahan')->values();

        // Petani yang sedang memiliki lahan ini
        $id_petani_saat_ini = $kepemilikan->id_petani;

        $petani = Petani::with('desa.kecamatan')
            ->where('id_petani', '!=', $id_petani_saat_ini)
            ->get();

        $desa = Desa::with('kecamatan')->get();
        $tahun_tanam = Tahun_Tanam::orderBy('tahun', 'desc')->get();
        $statusPengelolaanOptions = [
            'KSM',
            'Mandiri',
            'Perusahaan'
        ];

        return view('kepemilikan.edit_per_lahan', compact(
            'kepemilikan',
            'selectedDetail',
            'groupDetails',
            'petani',
            'desa',
            'tahun_tanam',
            'statusPengelolaanOptions'
        ));
    }

    public function updatePerLahan(Request $request, $id_kepemilikan, $id_lahan)
    {
        // Validasi input
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.status_pengelolaan' => 'required|in:KSM,Mandiri,Perusahaan',
        ]);

        // Update petani di kepemilikan utama
        $kepemilikan = Kepemilikan::findOrFail($id_kepemilikan);
        $kepemilikan->update([
            'id_petani' => $request->id_petani,
        ]);

        // Loop semua lahan
        foreach ($request->input('lahan') as $index => $data) {
            $detail = DetailKepemilikan::where('id_detail_kepemilikan', $data['id_detail_kepemilikan'])
                ->where('id_kepemilikan', $id_kepemilikan)
                ->first();

            if (!$detail) {
                continue;
            }

            // Update data lahan
            $lahan = $detail->lahan;
            $lahan->update([
                'id_desa' => $data['id_desa'],
                'id_tahun_tanam' => $data['id_tahun_tanam'],
                'luas_peta' => $data['luas_peta'],
            ]);

            $statusKepemilikan = $data['status_kepemilikan'];
            if ($data['status_pengelolaan'] === 'Perusahaan') {
                $statusKepemilikan = 'nonaktif';
            }
            // Update detail kepemilikan
            $detail->update([
                'kode_lahan' => $data['kode_lahan'],
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
                'status_pengelolaan' => $data['status_pengelolaan']

            ]);

            // === HANDLE FILE SHM ===
            if ($request->input("lahan.$index.hapus_shm") == 1) {
                if (!empty($detail->pdf_scan_shm) && Storage::disk('public')->exists($detail->pdf_scan_shm)) {
                    Storage::disk('public')->delete($detail->pdf_scan_shm);
                }
                $detail->update(['pdf_scan_shm' => null]);
            } elseif ($request->hasFile("lahan.$index.pdf_scan_shm")) {
                if (!empty($detail->pdf_scan_shm) && Storage::disk('public')->exists($detail->pdf_scan_shm)) {
                    Storage::disk('public')->delete($detail->pdf_scan_shm);
                }
                $path = $request->file("lahan.$index.pdf_scan_shm")->store('pdf_scan_shm', 'public');
                $detail->update(['pdf_scan_shm' => $path]);
            }

            // === HANDLE FILE PETA ===
            if ($request->input("lahan.$index.hapus_peta") == 1) {
                if (!empty($detail->pdf_scan_peta) && Storage::disk('public')->exists($detail->pdf_scan_peta)) {
                    Storage::disk('public')->delete($detail->pdf_scan_peta);
                }
                $detail->update(['pdf_scan_peta' => null]);
            } elseif ($request->hasFile("lahan.$index.pdf_scan_peta")) {
                if (!empty($detail->pdf_scan_peta) && Storage::disk('public')->exists($detail->pdf_scan_peta)) {
                    Storage::disk('public')->delete($detail->pdf_scan_peta);
                }
                $path = $request->file("lahan.$index.pdf_scan_peta")->store('pdf_scan_peta', 'public');
                $detail->update(['pdf_scan_peta' => $path]);
            }
        }

        // Sinkronisasi jumlah PBB
        $tahunSekarang = Carbon::now()->year;
        $pbb = Pbb::where('id_detail_kepemilikan', $detail->id_detail_kepemilikan)
            ->where('tahun', $tahunSekarang)
            ->first();

        if ($pbb) {
            if ($pbb->status === 'belum') {
                $pbb->update(['jumlah' => $data['jumlah_pbb'] ?? 0]);
            }
        } else {
            Pbb::create([
                'id_detail_kepemilikan' => $detail->id_detail_kepemilikan,
                'tahun' => $tahunSekarang,
                'jumlah' => $data['jumlah_pbb'] ?? 0,
                'status' => 'belum',
            ]);
        }

        return redirect()->route('kepemilikan.index', [
            'page' => $request->input('page'),
            'search' => $request->input('search'),
            'desa' => $request->input('desa'),
            'tahun' => $request->input('tahun'),
            'status_pengelolaan' => $request->input('status_pengelolaan'),
            'status' => $request->input('status'),
        ])->with('success', 'Data Kepemilikan Berhasil Diperbarui!');
    }

    public function destroyPerLahan($id_kepemilikan, $id_lahan)
    {
        DB::beginTransaction();

        try {
            // Cek apakah kepemilikan dan lahan cocok
            $detail = DetailKepemilikan::where('id_kepemilikan', $id_kepemilikan)
                ->where('id_lahan', $id_lahan)
                ->first();

            if (!$detail) {
                return redirect()->back()->with('error', 'Data lahan tidak ditemukan untuk kepemilikan ini.');
            }

            // Hapus detail kepemilikan (hanya lahan itu)
            $detail->delete();

            // Cek apakah masih ada detail lain untuk kepemilikan ini
            $sisaDetail = DetailKepemilikan::where('id_kepemilikan', $id_kepemilikan)->count();
            if ($sisaDetail == 0) {
                // Jika tidak ada, hapus kepemilikan induk
                Kepemilikan::find($id_kepemilikan)->delete();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Data lahan berhasil dihapus dari kepemilikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
        dd($request->all());
        $request->validate([
            'id_petani' => 'required|exists:petani,id_petani',
            'lahan.*.status_pengelolaan' => 'nullable|in:KSM,Mandiri,Perusahaan',
            'status_kepemilikan' => 'required|in:aktif,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lahan' => 'required|array',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.kode_lahan' => 'nullable|string|max:15',
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
            // 🔹 Buat kepemilikan dulu
            $kepemilikan = Kepemilikan::create([
                'id_petani' => $request->id_petani,
                'status_kepemilikan' => $request->status_kepemilikan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            // 🔹 Loop setiap lahan yang dikirim
            foreach ($request->lahan as $lahanData) {
                $lahan = Lahan::create([
                    'id_desa' => $lahanData['id_desa'],
                    'id_tahun_tanam' => $lahanData['id_tahun_tanam'],
                    'luas_peta' => $lahanData['luas_peta'],
                ]);

            $statusKepemilikan = match($lahanData['status_pengelolaan']) {
                'Perusahaan' => 'nonaktif',
                'Mandiri', 'KSM' => 'aktif',
            };

                // 🔹 Simpan detail kepemilikan
            DetailKepemilikan::create([
                'id_kepemilikan' => $kepemilikan->id_kepemilikan,
                'id_lahan' => $lahan->id_lahan,
                'kode_lahan' => $lahanData['kode_lahan'] ?? null,
                'nomor_SHM' => $lahanData['nomor_SHM'] ?? null,
                'nama_SHM' => $lahanData['nama_SHM'] ?? null,
                'nomor_sporadik' => $lahanData['nomor_sporadik'] ?? null,
                'nama_sporadik' => $lahanData['nama_sporadik'] ?? null,
                'nomor_kavling' => $lahanData['nomor_kavling'] ?? null,
                'luas_surat' => $lahanData['luas_surat'] ?? null,
                'nomor_pbb' => $lahanData['nomor_pbb'] ?? null,
                'jumlah_pbb' => $lahanData['jumlah_pbb'] ?? null,
                'status_pengelolaan' => $lahanData['status_pengelolaan'],
                'status_kepemilikan' => $statusKepemilikan, // <- ambil per lahan
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
    public function edit(Request $request, $id)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan' => function ($q) use ($id) {
                $q->where('id_kepemilikan', $id)
                    ->with(['lahan.desa.kecamatan', 'lahan.tahunTanam']);
            }
        ])->findOrFail($id);

        $page = $request->page;
        $search = $request->search;
        $desa = $request->desa;
        $tahun = $request->tahun;

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
            'lahan.*.status_pengelolaan' => 'required|in:KSM,Mandiri,Perusahaan',
            'lahan.*.id_lahan' => 'nullable|exists:lahan,id_lahan',
            'lahan.*.id_desa' => 'required|exists:desa,id_desa',
            'lahan.*.id_tahun_tanam' => 'required|exists:tahun_tanam,id_tahun_tanam',
            'lahan.*.luas_peta' => 'required|numeric|min:0',
            'lahan.*.kode_lahan' => 'nullable|string|max:15',
            'lahan.*.pdf_scan_shm' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.pdf_scan_peta' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.status_kepemilikan' => 'required|in:aktif,nonaktif',
            'lahan.*.tanggal_mulai' => 'nullable|date',
            'lahan.*.tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        // 🔹 Gunakan salinan array agar bisa dimodifikasi
        $lahanList = $request->lahan;

        foreach ($lahanList as $index => $lahanData) {
            // Kalau status_kepemilikan = nonaktif → otomatis status_pengelolaan = Perusahaan
            if ($lahanData['status_kepemilikan'] === 'nonaktif') {
                $lahanList[$index]['status_pengelolaan'] = 'Perusahaan';
            }

            // Kalau status_pengelolaan = Perusahaan → otomatis status_kepemilikan = nonaktif
            if ($lahanData['status_pengelolaan'] === 'Perusahaan') {
                $lahanList[$index]['status_kepemilikan'] = 'nonaktif';
            }
        }

        DB::beginTransaction();

        try {
            $kepemilikan = Kepemilikan::findOrFail($id);
            $kepemilikan->update([
                'id_petani' => $request->id_petani,
            ]);

            foreach ($lahanList as $index => $lahanData) {
                // jika lahan ditandai dihapus
                if (isset($lahanData['hapus']) && $lahanData['hapus'] == 1) {
                    if (!empty($lahanData['id_detail_kepemilikan'])) {
                        DetailKepemilikan::destroy($lahanData['id_detail_kepemilikan']);
                    }
                    continue;
                }

                // cari detail kepemilikan lama
                $detailId = $lahanData['id_detail_kepemilikan'] ?? null;

                if ($detailId) {
                    // data lama → update
                    $detail = DetailKepemilikan::find($detailId);
                    $lahan = Lahan::find($lahanData['id_lahan']);
                } else {
                    // data baru → buat baru
                    $lahan = new Lahan();
                    $detail = new DetailKepemilikan();
                    $detail->id_kepemilikan = $kepemilikan->id_kepemilikan;
                }

                // update data lahan
                $lahan->id_desa = $lahanData['id_desa'];
                $lahan->id_tahun_tanam = $lahanData['id_tahun_tanam'];
                $lahan->luas_peta = $lahanData['luas_peta'];
                $lahan->save();

                // === HANDLE FILE SHM ===
                if (!empty($lahanData['hapus_shm']) && $lahanData['hapus_shm'] == 1) {
                    if (!empty($detail->pdf_scan_shm) && Storage::disk('public')->exists($detail->pdf_scan_shm)) {
                        Storage::disk('public')->delete($detail->pdf_scan_shm);
                    }
                    $shmPath = null;
                } elseif ($request->hasFile("lahan.$index.pdf_scan_shm")) {
                    if (!empty($detail->pdf_scan_shm) && Storage::disk('public')->exists($detail->pdf_scan_shm)) {
                        Storage::disk('public')->delete($detail->pdf_scan_shm);
                    }
                    $shmPath = $request->file("lahan.$index.pdf_scan_shm")->store('shm_pdf', 'public');
                } else {
                    $shmPath = $detail->pdf_scan_shm ?? null;
                }

                // === HANDLE FILE PETA ===
                if (!empty($lahanData['hapus_peta']) && $lahanData['hapus_peta'] == 1) {
                    if (!empty($detail->pdf_scan_peta) && Storage::disk('public')->exists($detail->pdf_scan_peta)) {
                        Storage::disk('public')->delete($detail->pdf_scan_peta);
                    }
                    $petaPath = null;
                } elseif ($request->hasFile("lahan.$index.pdf_scan_peta")) {
                    if (!empty($detail->pdf_scan_peta) && Storage::disk('public')->exists($detail->pdf_scan_peta)) {
                        Storage::disk('public')->delete($detail->pdf_scan_peta);
                    }
                    $petaPath = $request->file("lahan.$index.pdf_scan_peta")->store('peta_pdf', 'public');
                } else {
                    $petaPath = $detail->pdf_scan_peta ?? null;
                }

                // Simpan detail
                $detail->fill([
                    'id_lahan' => $lahan->id_lahan,
                    'kode_lahan' => $lahanData['kode_lahan'],
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
                    'status_pengelolaan' => $lahanData['status_pengelolaan'],
                ]);
                $detail->save();
            }

            DB::commit();

            $queryParams = request()->only(['page', 'search', 'desa', 'tahun', 'status_pengelolaan', 'status']);
            return redirect()->route('kepemilikan.index', $queryParams)
                ->with('success', 'Data kepemilikan berhasil diperbarui.');

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

            // Hapus detail kepemilikan, tapi jangan hapus lahan!
            DetailKepemilikan::where('id_kepemilikan', $id)->delete();

            // Hapus kepemilikan induk
            $kepemilikan->delete();

            DB::commit();

            return redirect()->route('kepemilikan.index')
                ->with('success', 'Data kepemilikan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /*
     * function untuk menampilkan detail
     */
    public function show(Request $request, $id_kepemilikan)
    {
        $kepemilikan = Kepemilikan::with([
            'petani',
            'detailKepemilikan' => function ($q) use ($id_kepemilikan) {
                $q->where('id_kepemilikan', $id_kepemilikan)
                    ->with(['lahan.desa.kecamatan', 'lahan.tahunTanam', 'pbb']);
            }
        ])->findOrFail($id_kepemilikan);

        $page = $request->page;
        $search = $request->search;
        $desa = $request->desa;
        $tahun = $request->tahun;

        $tahunSekarang = Carbon::now()->year;

        $jumlahLahan = $kepemilikan->detailKepemilikan->count();
        if ($kepemilikan->petani->status !== 'berhenti') {
            if ($jumlahLahan === 0 && $kepemilikan->petani->status !== 'tidak_aktif') {
                $kepemilikan->petani->update(['status' => 'tidak_aktif']);
            } elseif ($jumlahLahan > 0 && $kepemilikan->petani->status !== 'aktif') {
                $kepemilikan->petani->update(['status' => 'aktif']);
            }
        }

        // Loop semua detail_kepemilikan
        foreach ($kepemilikan->detailKepemilikan as $detail) {
            // Cek apakah sudah ada data PBB untuk tahun ini
            $pbbTahunIni = $detail->pbb->where('tahun', $tahunSekarang)->first();

            if (!$pbbTahunIni) {
                // Buat data baru hanya kalau belum ada
                Pbb::create([
                    'id_detail_kepemilikan' => $detail->id_detail_kepemilikan,
                    'tahun' => $tahunSekarang,
                    'jumlah' => $detail->jumlah_pbb ?? 0, // Simpan jumlah saat ini
                    'status' => 'belum',
                ]);
            } else {
                // Update jumlah jika belum lunas dan nilainya berubah
                if ($pbbTahunIni->status === 'belum' && $pbbTahunIni->jumlah != $detail->jumlah_pbb) {
                    $pbbTahunIni->update([
                        'jumlah' => $detail->jumlah_pbb ?? 0,
                    ]);
                }
            }
        }
        $kepemilikan->petani->refresh();
        // Refresh relasi supaya data terbaru muncul di Blade
        $kepemilikan->load('detailKepemilikan.pbb');

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
        // Ambil detail yang diklik dulu
        $selectedDetail = DetailKepemilikan::with([
            'lahan.desa.kecamatan',
            'lahan.tahunTanam',
            'kepemilikan.petani',
            'pbb'
        ])->findOrFail($id_detail);

        $kepemilikan = $selectedDetail->kepemilikan;
        $petani = $kepemilikan->petani;

        // Tentukan desa & tahun dari lahan yang diklik
        $desaTarget = $selectedDetail->lahan->desa->desa ?? null;
        $tahunTarget = $selectedDetail->lahan->tahunTanam->tahun ?? null;

        // Ambil semua detail kepemilikan di desa & tahun yang sama
        $groupDetails = DetailKepemilikan::with(['lahan.desa.kecamatan', 'lahan.tahunTanam', 'pbb'])
            ->where('id_kepemilikan', $id_kepemilikan)
            ->whereHas('lahan', function ($q) use ($desaTarget, $tahunTarget) {
                $q->whereHas('desa', function ($qq) use ($desaTarget) {
                    $qq->where('desa', $desaTarget);
                })->whereHas('tahunTanam', function ($qq) use ($tahunTarget) {
                    $qq->where('tahun', $tahunTarget);
                });
            })->get();

        // Pastikan semua detail punya PBB tahun berjalan
        $tahunSekarang = Carbon::now()->year;
        foreach ($groupDetails as $detail) {
            $pbbTahunIni = $detail->pbb->firstWhere('tahun', $tahunSekarang);
            if (!$pbbTahunIni) {
                Pbb::create([
                    'id_detail_kepemilikan' => $detail->id_detail_kepemilikan,
                    'tahun' => $tahunSekarang,
                    'jumlah' => $detail->jumlah_pbb ?? 0,
                    'status' => 'belum',
                ]);
            }
        }

        // Refresh relasi pbb
        $groupDetails->load('pbb');

        // Generate PDF dengan semua detail
        $pdf = Pdf::loadView('kepemilikan.pdf_per_lahan', [
            'kepemilikan' => $kepemilikan,
            'groupDetails' => $groupDetails,
            'desaTarget' => $desaTarget,
            'tahunTarget' => $tahunTarget,
        ])->setPaper('a4', 'portrait');

        $pathMain = storage_path('app/public/kepemilikan_per_lahan.pdf');
        $pdf->save($pathMain);

        // Lampiran PDF KTP/KK
        $lampiranFiles = [];
        if ($petani->pdf_scan_ktp && file_exists(storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_ktp))) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_ktp);
        }
        if ($petani->pdf_scan_kk && file_exists(storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_kk))) {
            $lampiranFiles[] = storage_path('app/public/ktp_pdf/' . $petani->pdf_scan_kk);
        }

        // Lampiran PDF SHM & Peta untuk semua lahan
        foreach ($groupDetails as $detail) {
            if ($detail->pdf_scan_shm && file_exists(storage_path('app/public/' . $detail->pdf_scan_shm))) {
                $lampiranFiles[] = storage_path('app/public/' . $detail->pdf_scan_shm);
            }
            if ($detail->pdf_scan_peta && file_exists(storage_path('app/public/' . $detail->pdf_scan_peta))) {
                $lampiranFiles[] = storage_path('app/public/' . $detail->pdf_scan_peta);
            }
        }

        // Merge PDF seperti sebelumnya
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
            . ' - ' . $desaTarget
            . ' (' . $tahunTarget . ').pdf'
        );
    }

    public function cetakSemuaPDF(Request $request)
    {
        $statusPetani = $request->input('status_petani', 'aktif');

        $query = Kepemilikan::with([
            'petani.desa.kecamatan',
            'detailKepemilikan' => function ($q) use ($request) {
                if ($request->filled('status_pengelolaan')) {
                    $q->whereIn('status_pengelolaan', (array) $request->status_pengelolaan);
                }
            },
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
                    })
                    ->orWhereHas('detailKepemilikan', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(kode_lahan) like ?', ["%{$search}%"]);
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

        // ===================== FILTER STATUS PETANI =====================
        if (!empty($request->status_petani) && strtolower($request->status_petani) === 'berhenti') {
            // Petani berhenti → punya lahan tapi nonaktif
            $query->whereHas('petani', function ($q) {
                $q->where('status', 'berhenti');
            })->whereHas('detailKepemilikan', function ($q) {
                $q->where('status_kepemilikan', 'nonaktif');
            });
        } else {
            // Default → petani & lahan aktif
            $query->whereHas('petani', function ($q) {
                $q->where('status', 'aktif');
            })->whereHas('detailKepemilikan', function ($q) {
                $q->where('status_kepemilikan', 'aktif');
            });
        }

        // ===================== FILTER STATUS PENGELOLAAN =====================
        $statusPengelolaan = $request->filled('status_pengelolaan')
            ? (array) $request->input('status_pengelolaan', [])
            : ['KSM', 'Mandiri'];

        $query->whereHas('detailKepemilikan', fn($q2) => $q2->whereIn('status_pengelolaan', $statusPengelolaan));


        //Ambil hasil akhir
        $kepemilikan = $query
            ->orderBy(
                Petani::select('nomor_anggota_plasma')
                    ->whereColumn('petani.id_petani', 'kepemilikan.id_petani')
                    ->limit(1)
            )
            ->get();

        if ($kepemilikan->isEmpty()) {
            return back()->with('error', 'Tidak ada data yang cocok dengan filter atau pencarian.');
        }

        // Filter ulang detail agar sesuai juga
        $kepemilikan->each(function ($kep) use ($request, $statusPengelolaan) {
            $kep->detailKepemilikan = $kep->detailKepemilikan
                ->whereIn('status_pengelolaan', $statusPengelolaan)
                ->filter(function ($detail) use ($request) {
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

        $namaDesa = $request->filled('desa') ? str_replace(' ', '_', $request->desa) : 'Semua Desa';
        $namaTahun = $request->filled('tahun') ? $request->tahun : 'Semua Tahun';
        $jenisKelola = $request->filled('status_pengelolaan') ? $request->status_pengelolaan : "Semua Kelola";

        $namaFile = "Data_Kepemilikan_{$namaDesa}_{$namaTahun}_{$jenisKelola}.pdf";

        return $pdf->download($namaFile);
    }

    // data PBB per lahan //
    //Menandai PBB tahun tertentu sebagai lunas
    public function tandaiLunasPbb($id_pbb)
    {
        $pbb = Pbb::findOrFail($id_pbb);
        $pbb->update(['status' => 'lunas']);

        // Ambil ulang detail kepemilikan dan relasinya
        $detail = $pbb->detailKepemilikan()->with([
            'lahan.desa.kecamatan',
            'lahan.tahunTanam',
            'pbb'
        ])->first();

        // Refresh data agar view langsung pakai data terkini
        $detail->refresh();

        return redirect()
            ->route('kepemilikan.showPerLahan', [
                'id_kepemilikan' => $detail->id_kepemilikan,
                'id_lahan' => $detail->id_lahan
            ])
            ->with('success', 'PBB tahun ' . $pbb->tahun . ' telah ditandai lunas.');
    }

    //Membuat akumulasi otomatis untuk tahun berikutnya
    public function generatePbbTahunBaru($id_detail_kepemilikan)
    {

        $tahunSekarang = Carbon::now()->year;
        $bulanSekarang = Carbon::now()->month;

        if ($bulanSekarang != 10) {
            return back()->with('error', 'Akumulasi PBB hanya dapat dilakukan pada bulan Oktober.');
        }

        $detail = DetailKepemilikan::with('pbb')->findOrFail($id_detail_kepemilikan);
        $jumlahPBBBaru = $detail->jumlah_pbb ?? 0;

        $tahunDepan = $tahunSekarang + 1;

        // Cek PBB tahun ini
        $pbbTahunIni = $detail->pbb->where('tahun', $tahunSekarang)->first();

        // Jika tahun ini belum lunas, tambahkan ke akumulasi
        if ($pbbTahunIni && $pbbTahunIni->status == 'belum') {
            $jumlahPBBBaru += $pbbTahunIni->jumlah;
        }

        // Cek data tahun depan
        $pbbTahunDepan = $detail->pbb->where('tahun', $tahunDepan)->first();

        if ($pbbTahunDepan) {
            // update hanya kalau memang berbeda
            $pbbTahunDepan->update([
                'jumlah' => $jumlahPBBBaru,
                'status' => 'belum',
            ]);
        } else {
            // buat baru
            Pbb::create([
                'id_detail_kepemilikan' => $detail->id_detail_kepemilikan,
                'tahun' => $tahunDepan,
                'jumlah' => $jumlahPBBBaru,
                'status' => 'belum',
            ]);
        }

        $detail->load('pbb');

        return back()->with('success', 'PBB tahun ' . $tahunDepan . ' berhasil dibuat atau diperbarui.');
    }

    public function gantiKepemilikan(Request $request, $id_lahan)
    {
        $request->validate([
            'nomor_anggota_plasma' => 'required|string',
            'nomor_anggota_koperasi' => 'required|string',
            'NIK' => 'required|string|max:16',
            'nama' => 'required|string',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,tidak_aktif',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/', // validasi angka & +62
            'pdf_scan_ktp' => 'nullable|mimes:pdf|max:10240',
            'pdf_scan_kk' => 'nullable|mimes:pdf|max:10240',
        ]);

        // Format nomor telepon
        $no_telepon = $request->no_telepon;
        if ($no_telepon) {
            if (substr($no_telepon, 0, 1) === '0') {
                $no_telepon = '+62' . substr($no_telepon, 1);
            }
        }
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
            'alamat' => $request->alamat ?? null,
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
            ->orderByDesc('id_riwayat')
            ->get();

        $lahan = Lahan::with(['detailKepemilikan.kepemilikan.petani', 'desa.kecamatan'])
            ->findOrFail($id_lahan);

        // Ambil ID Kepemilikan aktif saat ini
        $id_kepemilikan = $lahan->detailKepemilikan->last()->id_kepemilikan ?? null;

        // Ambil petani terakhir (yang memegang lahan sekarang)
        $petaniSekarang = $riwayat->sortBy('tanggal_ganti')->last()?->petaniSesudah;

        return view('kepemilikan.riwayat_lahan', compact('riwayat', 'lahan', 'petaniSekarang', 'id_kepemilikan'));
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
            'nomor_anggota_plasma' => 'nullable|required_if:mode,baru|string|max:100',
            'nomor_anggota_koperasi' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/', // validasi nomor telepon
            'tanggal_ganti' => 'nullable|date',
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

            // Buat petani baru dengan assign nama file PDF
            $petaniBaru = Petani::create([
                'nomor_anggota_plasma' => $validated['nomor_anggota_plasma'],
                'nomor_anggota_koperasi' => $validated['nomor_anggota_koperasi'],
                'NIK' => $validated['NIK'],
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'] ?? null,
                'status' => $validated['status'] ?? 'aktif',
                'no_telepon' => $no_telepon,
                'pdf_scan_ktp' => $ktpName,
                'pdf_scan_kk' => $kkName,
            ]);

            $id_petani_sesudah = $petaniBaru->id_petani;
        }

        // Cari atau buat kepemilikan untuk petani baru
        $kepemilikanBaru = Kepemilikan::firstOrCreate(
            ['id_petani' => $id_petani_sesudah],
            ['tanggal_kepemilikan' => now(), 'status' => 'aktif']
        );

        // Pindahkan lahan ke petani baru
        // Pindahkan lahan ke petani baru
        $detail->update([
            'id_kepemilikan' => $kepemilikanBaru->id_kepemilikan,
        ]);

        // Cek kepemilikan lama, hapus jika sudah tidak punya detail
        $kepemilikan->refresh(); // reload relasi
        if ($kepemilikan->detailKepemilikan()->count() === 0) {
            $kepemilikan->delete();
        }

        // Simpan riwayat perpindahan
        RiwayatKepemilikan::create([
            'id_lahan' => $id_lahan,
            'id_petani_sebelum' => $id_petani_sebelum,
            'id_petani_sesudah' => $id_petani_sesudah,
            'tanggal_ganti' => $validated['tanggal_ganti'] ?? null,
            'keterangan' => $validated['keterangan'] ?? 'Perubahan kepemilikan lahan',
        ]);


        return redirect()->route('kepemilikan.editPerLahan', [$kepemilikanBaru->id_kepemilikan, $id_lahan])
            ->with('success', 'Kepemilikan lahan berhasil dipindahkan.');
    }

    public function updateKepemilikanSemua(Request $request, $id_kepemilikan)
    {
        $kepemilikan = Kepemilikan::with('detailKepemilikan', 'petani')->findOrFail($id_kepemilikan);

        if ($kepemilikan->detailKepemilikan->count() === 0) {
            return back()->with('error', 'Petani ini tidak memiliki lahan.');
        }

        $validated = $request->validate([
            'mode' => 'required|in:lama,baru',
            'id_petani_baru' => 'nullable|required_if:mode,lama|exists:petani,id_petani',
            'nama' => 'nullable|string|max:255',
            'NIK' => 'nullable|string|max:16',
            'nomor_anggota_plasma' => 'nullable|string|max:100',
            'nomor_anggota_koperasi' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/',
            'tanggal_ganti' => 'nullable|date',
            'pdf_scan_ktp' => 'nullable|mimes:pdf|max:10240',
            'pdf_scan_kk' => 'nullable|mimes:pdf|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        // Petani lama
        $id_petani_sebelum = $kepemilikan->id_petani;

        // ambil nomor plasma petani lama
        $nomor_plasma_lama = $kepemilikan->petani->nomor_anggota_plasma;

        // Tentukan petani baru
        if ($validated['mode'] === 'lama') {

            $id_petani_sesudah = $validated['id_petani_baru'];
            
        } else {

            // Format nomor telepon
            $no_telepon = $validated['no_telepon'] ?? null;
            if ($no_telepon && substr($no_telepon, 0, 1) === '0') {
                $no_telepon = '+62' . substr($no_telepon, 1);
            }

            // Upload file PDF
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

            // Hapus nomor_plasma dari petani lama
            Petani::where('id_petani', $id_petani_sebelum)
                ->update(['nomor_anggota_plasma' => null]);

            // Buat petani baru
            $petaniBaru = Petani::create([
                'nomor_anggota_plasma' => $nomor_plasma_lama,
                'nomor_anggota_koperasi' => $validated['nomor_anggota_koperasi'],
                'NIK' => $validated['NIK'],
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'] ?? null,
                'status' => $validated['status'] ?? 'aktif',
                'no_telepon' => $no_telepon,
                'pdf_scan_ktp' => $ktpName,
                'pdf_scan_kk' => $kkName,
            ]);

            // petani baru dibuat
            $id_petani_sesudah = $petaniBaru->id_petani;
        }

        // Buat kepemilikan baru jika belum ada
        $kepemilikanBaru = Kepemilikan::firstOrCreate(
            ['id_petani' => $id_petani_sesudah],
            ['tanggal_kepemilikan' => now(), 'status' => 'aktif']
        );

        // Pindahkan SEMUA lahan yang dimiliki petani lama
        foreach ($kepemilikan->detailKepemilikan as $detail) {

            // Update kepemilikan lahan
            $detail->update([
                'id_kepemilikan' => $kepemilikanBaru->id_kepemilikan,
            ]);

            // Simpan riwayat perpindahan tiap lahan
            RiwayatKepemilikan::create([
                'id_lahan' => $detail->id_lahan,
                'id_petani_sebelum' => $id_petani_sebelum,
                'id_petani_sesudah' => $id_petani_sesudah,
                'tanggal_ganti' => $validated['tanggal_ganti'] ?? null,
                'keterangan' => $validated['keterangan'] ?? 'Perubahan kepemilikan lahan',
            ]);
        }

        // Setelah semua dipindah → hapus kepemilikan lama
        $kepemilikan->delete();

        return redirect()->route('kepemilikan.edit', $kepemilikanBaru->id_kepemilikan)
            ->with('success', 'Kepemilikan lahan berhasil dipindahkan.');

    }


    public function updateRiwayat(Request $request, $id)
    {
        $request->validate([
            'tanggal_ganti' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        $riwayat = RiwayatKepemilikan::findOrFail($id);

        $riwayat->update([
            'tanggal_ganti' => $request->tanggal_ganti ?? null,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Riwayat kepemilikan berhasil diperbarui.');
    }

    // HAPUS RIWAYAT
    public function deleteRiwayat($id)
    {
        RiwayatKepemilikan::findOrFail($id)->delete();

        return back()->with('success', 'Riwayat kepemilikan berhasil dihapus.');
    }

}