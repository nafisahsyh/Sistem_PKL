<?php

namespace App\Http\Controllers;

use App\Models\Petani;
use App\Models\Desa;
use App\Models\Tahun_Tanam;
use App\Models\Kepemilikan;
use App\Models\DetailKepemilikan;
use App\Models\Lahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PetaniController extends Controller
{
    public function index(Request $request)
    {
        $query = Petani::withCount('kepemilikan');

        //Filter berdasarkan status (aktif / tidak_aktif / berhenti)
        $status = $request->query('status');
        if ($status) {
            if (is_array($status)) {
                // format: ?status[]=tidak_aktif&status[]=berhenti
                $query->whereIn('status', $status);
            } else {
                // format: ?status=aktif
                $query->where('status', $status);
            }
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('NIK', 'like', "%{$keyword}%")
                    ->orWhere('nomor_anggota_plasma', 'like', "%{$keyword}%")
                    ->orWhere('nomor_anggota_koperasi', 'like', "%{$keyword}%");
            });
        }

        // Urutkan & paginasi
        $petani = $query->orderBy('id_petani', 'desc')->paginate(15);
        $petani->appends($request->only(['search', 'status']));

        return view('petani.index', compact('petani', 'status'));
    }

    public function create()
    {
        return view('petani.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_anggota_plasma' => 'nullable|string|max:100|unique:petani',
            'nomor_anggota_koperasi' => 'nullable|string|max:100',
            'NIK' => 'nullable|string|size:16|unique:petani',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif, berhenti',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/', // validasi angka & +62
            'pdf_scan_ktp' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_scan_kk' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        // format nomor telepon
        $no_telepon = $request->no_telepon;
        if ($no_telepon) {
            if (substr($no_telepon, 0, 1) === '0') {
                $no_telepon = '+62' . substr($no_telepon, 1);
            }
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

        Petani::create([
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

        return redirect()->route('petani.index')->with('success', 'Data petani berhasil ditambahkan.');
    }

    public function edit(Petani $petani, Request $request)
    {
        $page = $request->query('page', 1); // ambil page dari query string, default 1
        return view('petani.edit', compact('petani', 'page'));
    }

    public function update(Request $request, Petani $petani)
    {
        $request->validate([
            'nomor_anggota_plasma' => 'nullable|string|max:100|unique:petani,nomor_anggota_plasma,' . $petani->id_petani . ',id_petani',
            'nomor_anggota_koperasi' => 'nullable|string|max:100',
            'NIK' => 'nullable|string|size:16|unique:petani,NIK,' . $petani->id_petani . ',id_petani',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif,berhenti',
            'no_telepon' => 'nullable|regex:/^\+?[0-9]+$/',
            'pdf_scan_ktp' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_scan_kk' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        // Format nomor telepon
        $no_telepon = $request->no_telepon;
        if ($no_telepon && substr($no_telepon, 0, 1) === '0') {
            $no_telepon = '+62' . substr($no_telepon, 1);
        }

        $ktpName = $petani->pdf_scan_ktp;
        $kkName = $petani->pdf_scan_kk;

        // Hapus file KTP
        if ($request->hapus_ktp == 1 && $petani->pdf_scan_ktp) {
            Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_ktp);
            $ktpName = null;
        }

        // Hapus file KK
        if ($request->hapus_kk == 1 && $petani->pdf_scan_kk) {
            Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_kk);
            $kkName = null;
        }

        // Upload KTP baru
        if ($request->hasFile('pdf_scan_ktp')) {
            if ($ktpName && Storage::disk('public')->exists('ktp_pdf/' . $ktpName)) {
                Storage::disk('public')->delete('ktp_pdf/' . $ktpName);
            }
            $ktpName = time() . '_' . $request->file('pdf_scan_ktp')->getClientOriginalName();
            $request->file('pdf_scan_ktp')->storeAs('ktp_pdf', $ktpName, 'public');
        }

        // Upload KK baru
        if ($request->hasFile('pdf_scan_kk')) {
            if ($kkName && Storage::disk('public')->exists('ktp_pdf/' . $kkName)) {
                Storage::disk('public')->delete('ktp_pdf/' . $kkName);
            }
            $kkName = time() . '_' . $request->file('pdf_scan_kk')->getClientOriginalName();
            $request->file('pdf_scan_kk')->storeAs('ktp_pdf', $kkName, 'public');
        }

        /*=================== GABUNGKAN SEMUA UPDATE DALAM SATU ARRAY =============*/
        $dataUpdate = [
            'nomor_anggota_plasma' => $request->nomor_anggota_plasma,
            'nomor_anggota_koperasi' => $request->nomor_anggota_koperasi,
            'NIK' => $request->NIK,
            'nama' => $request->nama,
            'alamat' => $request->alamat ?? null,
            'no_telepon' => $no_telepon,
            'pdf_scan_ktp' => $ktpName,
            'pdf_scan_kk' => $kkName,
        ];

        $dataUpdate['status'] = $request->status;

        // // Status tidak berubah jika sudah berhenti
        // if ($petani->status !== 'berhenti') {
        //     $dataUpdate['status'] = $request->status;
        // }

        // UPDATE SEKALI SAJA
        $petani->update($dataUpdate);

        // Redirect, tetap di page yang sama
        $page = $request->input('page', 1);
        return redirect()->route('petani.index', ['page' => $page])
            ->with('success', 'Data petani berhasil diperbarui.');
    }

    public function destroy(Petani $petani)
    {
        $petani->delete(); // semua file & kepemilikan terkait akan otomatis terhapus
        return redirect()->route('petani.index')->with('success', 'Data petani berhasil dihapus.');
    }

    public function tambahKepemilikan($id_petani)
    {
        $petani = Petani::with('desa.kecamatan')->findOrFail($id_petani);
        $desa = Desa::with('kecamatan')->get();
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
            'lahan.*.status_pengelolaan' => 'nullable|in:KSM,Mandiri,Perusahaan',
            'lahan.*.luas_peta' => 'required|numeric|min:0',
            'lahan.*.kode_lahan' => 'nullable|string|max:15',
            'lahan.*.nomor_SHM' => 'nullable|string|max:100',
            'lahan.*.nama_SHM' => 'nullable|string|max:100',
            'lahan.*.nomor_sporadik' => 'nullable|string|max:100',
            'lahan.*.nama_sporadik' => 'nullable|string|max:100',
            'lahan.*.nomor_kavling' => 'nullable|string|max:100',
            'lahan.*.luas_surat' => 'nullable|numeric|min:0',
            'lahan.*.nomor_pbb' => 'nullable|string|max:100',
            'lahan.*.jumlah_pbb' => 'nullable|numeric|min:0',
            'lahan.*.koordinat_x' => 'nullable|numeric|between:-180,180',
            'lahan.*.koordinat_y' => 'nullable|numeric|between:-90,90',
            'lahan.*.posisi surat' => 'nullable|in:Notaris,PTP,Koperasi,Petani',
            'lahan.*.status_penyerahan' => 'nullable|string|max:100',
            'lahan.*.pdf_scan_shm' => 'nullable|file|mimes:pdf|max:30720',
            'lahan.*.pdf_scan_peta' => 'nullable|file|mimes:pdf|max:10240',
            'lahan.*.status_kepemilikan' => 'nullable|in:aktif,nonaktif',
            'lahan.*.tanggal_mulai' => 'nullable|date',
            'lahan.*.tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();
        try {
            $kepemilikan = Kepemilikan::create([
                'id_petani' => $request->id_petani,
            ]);

            foreach ($request->lahan as $lahanData) {
                // ambil status dari input
                $statusKepemilikan = $lahanData['status_kepemilikan'] ?? null;

                // ambil dari input user dulu, jangan paksa KSM
                $statusPengelolaan = $lahanData['status_pengelolaan'] ?? null;

                // sinkronisasi hanya berlaku jika status kepemilikan nonaktif dan bukan Mandiri
                if ($statusPengelolaan === 'Perusahaan') {
                    $statusKepemilikan = 'nonaktif';
                }
                // jika tetap null (user tidak pilih), baru default ke KSM
                $statusPengelolaan = $statusPengelolaan ?? 'KSM';

                // simpan data lahan
                $lahan = Lahan::create([
                    'id_desa' => $lahanData['id_desa'],
                    'id_tahun_tanam' => $lahanData['id_tahun_tanam'],
                    'luas_peta' => $lahanData['luas_peta'],
                ]);

                // simpan file pdf kalau ada
                $shmName = null;
                if (!empty($lahanData['pdf_scan_shm']) && $lahanData['pdf_scan_shm']->isValid()) {
                    $shmName = $lahanData['pdf_scan_shm']->store('shm_pdf', 'public');
                }

                $petaName = null;
                if (!empty($lahanData['pdf_scan_peta']) && $lahanData['pdf_scan_peta']->isValid()) {
                    $petaName = $lahanData['pdf_scan_peta']->store('peta_pdf', 'public');
                }

                // buat detail kepemilikan
                DetailKepemilikan::create([
                    'id_kepemilikan' => $kepemilikan->id_kepemilikan,
                    'id_lahan' => $lahan->id_lahan,
                    'kode_lahan' => $lahanData['kode_lahan'],
                    'posisi_surat' => $lahanData['posisi_surat'] ?? null,
                    'status_penyerahan' => $lahanData['status_penyerahan'] ?? null,
                    'nomor_SHM' => $lahanData['nomor_SHM'] ?? null,
                    'nama_SHM' => $lahanData['nama_SHM'] ?? null,
                    'nomor_sporadik' => $lahanData['nomor_sporadik'] ?? null,
                    'nama_sporadik' => $lahanData['nama_sporadik'] ?? null,
                    'nomor_kavling' => $lahanData['nomor_kavling'] ?? null,
                    'luas_surat' => $lahanData['luas_surat'] ?? null,
                    'nomor_pbb' => $lahanData['nomor_pbb'] ?? null,
                    'jumlah_pbb' => $lahanData['jumlah_pbb'] ?? null,
                    'koordinat_x' => $lahanData['koordinat_x'] ?? null,
                    'koordinat_y' => $lahanData['koordinat_y'] ?? null,
                    'pdf_scan_shm' => $shmName,
                    'pdf_scan_peta' => $petaName,
                    'status_kepemilikan' => $statusKepemilikan,
                    'tanggal_mulai' => $lahanData['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $lahanData['tanggal_selesai'] ?? null,
                    'status_pengelolaan' => $statusPengelolaan,
                ]);
            }

            // cek status petani
            // $adaLahanAktif = $kepemilikan->petani->kepemilikan()
            //     ->whereHas('detailKepemilikan', fn($q) => $q->where('status_kepemilikan', 'aktif'))
            //     ->exists();

            // if (!$adaLahanAktif) {
            //     $kepemilikan->petani->status = 'berhenti';
            //     $kepemilikan->petani->save();
            // }

            DB::commit();

            // ===================== QUERY SAMA PERSIS DENGAN INDEX =====================
            $pageQuery = Kepemilikan::select('kepemilikan.id_kepemilikan')
                ->leftJoin('petani', 'kepemilikan.id_petani', '=', 'petani.id_petani')
                ->leftJoin('detail_kepemilikan', 'detail_kepemilikan.id_kepemilikan', '=', 'kepemilikan.id_kepemilikan')
                ->leftJoin('lahan', 'lahan.id_lahan', '=', 'detail_kepemilikan.id_lahan')
                ->leftJoin('desa', 'desa.id_desa', '=', 'lahan.id_desa')
                ->leftJoin('tahun_tanam', 'tahun_tanam.id_tahun_tanam', '=', 'lahan.id_tahun_tanam')
                ->orderBy('petani.nomor_anggota_plasma', 'asc');

            // ===================== FILTER STATUS PETANI =====================
            if (strtolower($request->status_petani) === 'berhenti') {

                $pageQuery->where('petani.status', 'berhenti');
            } else {
                $pageQuery->where('petani.status', 'aktif');
            }

            // ===================== FILTER SEARCH =====================
            if (!empty($request->search)) {
                $search = strtolower($request->search);

                $pageQuery->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(petani.nama) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(petani.nomor_anggota_plasma) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(petani.nomor_anggota_koperasi) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(desa.desa) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(tahun_tanam.tahun) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(detail_kepemilikan.kode_lahan) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(detail_kepemilikan.status_pengelolaan) like ?', ["%{$search}%"]);
                });
            }

            // ===================== FILTER STATUS PENGELOLAAN =====================
            if (!empty($request->status_pengelolaan) && strtolower($request->status_pengelolaan) !== 'semua') {
                $pageQuery->whereRaw('LOWER(detail_kepemilikan.status_pengelolaan) = ?', [
                    strtolower($request->status_pengelolaan)
                ]);
            }

            // ===================== FILTER DESA =====================
            if (!empty($request->desa) && strtolower($request->desa) !== 'semua') {
                $pageQuery->where('desa.desa', $request->desa);
            }

            // ===================== FILTER TAHUN TANAM =====================
            if (!empty($request->tahun) && strtolower($request->tahun) !== 'semua') {
                $pageQuery->where('tahun_tanam.tahun', $request->tahun);
            }

            // ===================== ONLY DATA YANG PUNYA DETAIL =====================
            $pageQuery->whereNotNull('detail_kepemilikan.id_detail_kepemilikan');

            // ===================== AMBIL ID BERDASARKAN FILTER INDEX =====================
            $filteredIds = $pageQuery->pluck('kepemilikan.id_kepemilikan')->unique()->values()->toArray();

            // ===================== CARI POSISI DATA BARU =====================
            $position = array_search($kepemilikan->id_kepemilikan, $filteredIds);

            // Jika tidak ketemu (harusnya tidak mungkin)
            if ($position === false) {
                $page = 1;
            } else {
                $page = ceil(($position + 1) / 10);
            }

            // ===================== REDIRECT DENGAN FILTER LENGKAP =====================
            return redirect()->route('kepemilikan.index', [
                'page' => $page,
                'search' => $request->search,
                'desa' => $request->desa,
                'tahun' => $request->tahun,
                'status_pengelolaan' => $request->status_pengelolaan,
                'status_petani' => $request->status_petani,
            ])->with('success', 'Data kepemilikan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
