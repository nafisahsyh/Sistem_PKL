@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        {{-- button back --}}
        <div class="d-flex align-items-center mb-4">
            {{-- Tombol Back --}}
            <a href="{{ route('kepemilikan.index', [
                'page' => request('page'),
                'search' => request('search'),
                'desa' => request('desa'),
                'tahun' => request('tahun'),
            ]) }}"
                class="btn btn-success p-2" title="Kembali ke Data Kepemilikan">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            {{-- Judul Halaman --}}
            <h4 class="text-brown mb-0 ms-2">Edit Data Kepemilikan</h4>
        </div>

        {{-- ALERT PESAN --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ==== FORM EDIT PER LAHAN ==== --}}
        <form
            action="{{ route('kepemilikan.updatePerLahan', [$kepemilikan->id_kepemilikan, $selectedDetail->lahan->id_lahan]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="id_petani" value="{{ $kepemilikan->id_petani }}">

            {{-- ===================== DATA PETANI ===================== --}}
            <div class="card p-4 mb-4 shadow-sm rounded-4 border-0">
                <h5 class="text-green-custom mb-3">Data Petani</h5>

                <div class="p-4 bg-light rounded-3 border">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Status Petani</div>

                            @php
                                $status2 = strtolower($kepemilikan->petani->status ?? '-');
                                $label2 = ucwords(str_replace('_', ' ', $status2));

                                $color2 = match ($status2) {
                                    'aktif' => 'bg-success',
                                    'tidak_aktif' => 'bg-secondary',
                                    'berhenti' => 'bg-danger',
                                    default => 'bg-dark',
                                };
                            @endphp

                            <span class="badge {{ $color2 }} px-3 py-2">
                                {{ $label2 }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nomor Plasma</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nomor Koperasi</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">NIK</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $kepemilikan->petani->NIK ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nama Petani</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $kepemilikan->petani->nama ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nomor Telepon</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $kepemilikan->petani->no_telepon ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-dark fw-semibold">Alamat</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $kepemilikan->petani->alamat ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="my-3" style="height: 1px; background: #e5e5e5;"></div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Scan KTP</div>
                            <div>
                                @if ($kepemilikan->petani->pdf_scan_ktp ?? false)
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}"
                                        target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-file-pdf"></i> Lihat KTP
                                    </a>
                                @else
                                    <span class="text-muted fw-medium">Tidak ada dokumen</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Scan KK</div>
                            <div>
                                @if ($kepemilikan->petani->pdf_scan_kk ?? false)
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_kk) }}"
                                        target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-file-pdf"></i> Lihat KK
                                    </a>
                                @else
                                    <span class="text-muted fw-medium">Tidak ada dokumen</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ===================== DATA LAHAN ===================== --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-green-custom mb-3">Data Lahan & Detail Kepemilikan</h5>
                </div>

                {{-- Filter hanya lahan yang sesuai --}}
                <div id="lahan-container">
                    @foreach ($groupDetails as $index => $detail)
                        <div class="border rounded p-3 mb-4 bg-light lahan-item" data-lahan-index="{{ $index }}">
                            {{-- Hidden ID --}}
                            <input type="hidden" name="lahan[{{ $index }}][id_detail_kepemilikan]"
                                value="{{ $detail->id_detail_kepemilikan }}">
                            <input type="hidden" name="lahan[{{ $index }}][id_lahan]"
                                value="{{ $detail->lahan->id_lahan }}">
                            <input type="hidden" name="lahan[{{ $loop->index }}][hapus_shm]" class="hapus_shm"
                                value="0">
                            <input type="hidden" name="lahan[{{ $loop->index }}][hapus_peta]" class="hapus_peta"
                                value="0">

                            <input type="hidden" name="lahan[{{ $index }}][hapus]" value="0"
                                class="hapus-input">

                            {{-- Tombol hapus & ganti kepemilikan --}}
                            <div class="d-flex justify-content-end mb-2">
                                @if (count($kepemilikan->detailKepemilikan) > 1)
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-lahan me-2">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-warning btn-ganti-lahan" data-bs-toggle="modal"
                                    data-bs-target="#modalGantiKepemilikan"
                                    data-lahan-id="{{ optional($detail->lahan)->id_lahan }}"
                                    onclick="setLahanId({{ optional($detail->lahan)->id_lahan }})">
                                    <i class="fas fa-sync-alt"></i> Ganti Kepemilikan
                                </button>
                            </div>

                            <h6 class="text-brown mb-3">Lahan {{ $index + 1 }}</h6>

                            {{-- Informasi dasar lahan --}}
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Desa</label>
                                    <select name="lahan[{{ $index }}][id_desa]"
                                        class="form-select text-kecil choices-select choices-search"
                                        data-placeholder="Pilih Desa" data-search-placeholder="Cari Desa...">
                                        @foreach ($desa as $d)
                                            <option value="{{ $d->id_desa }}"
                                                {{ $detail->lahan->id_desa == $d->id_desa ? 'selected' : '' }}>
                                                {{ $d->desa }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tahun Tanam</label>
                                    <select name="lahan[{{ $index }}][id_tahun_tanam]"
                                        class="form-select text-kecil choices-select choices-search"
                                        data-placeholder="Pilih Tahun Tanam" data-search-placeholder="Cari Tahun...">
                                        @foreach ($tahun_tanam as $t)
                                            <option value="{{ $t->id_tahun_tanam }}"
                                                {{ $detail->lahan->id_tahun_tanam == $t->id_tahun_tanam ? 'selected' : '' }}>
                                                {{ $t->tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Kode</label>
                                    <input type="text" name="lahan[{{ $index }}][kode_lahan]"
                                        class="form-control text-kecil" value="{{ $detail->kode_lahan }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nomor Kavling</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_kavling]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_kavling }}">
                                </div>
                            </div>

                            {{-- Nomor dokumen --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nomor SHM</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_SHM]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_SHM }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Sesuai SHM</label>
                                    <input type="text" name="lahan[{{ $index }}][nama_SHM]"
                                        class="form-control text-kecil" value="{{ $detail->nama_SHM }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Luas Sesuai Lapangan (M²)<span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][luas_peta]"
                                        class="form-control text-kecil" value="{{ $detail->lahan->luas_peta }}" required>
                                </div>
                            </div>

                            {{-- Sporadik & PBB --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nomor Sporadik</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_sporadik]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_sporadik }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Sesuai Sporadik</label>
                                    <input type="text" name="lahan[{{ $index }}][nama_sporadik]"
                                        class="form-control text-kecil" value="{{ $detail->nama_sporadik }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Luas Sesuai Surat (M²)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][luas_surat]"
                                        class="form-control text-kecil" value="{{ $detail->luas_surat }}">
                                </div>
                            </div>

                            {{-- Luas & PBB --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status Kelola</label>
                                    <select name="lahan[{{ $index }}][status_pengelolaan]"
                                        class="form-select text-kecil choices-select">
                                        <option value="">Pilih Status Kelola</option>
                                        <option value="KSM"
                                            {{ $detail->status_pengelolaan == 'KSM' ? 'selected' : '' }}>KSM
                                        </option>
                                        <option value="Mandiri"
                                            {{ $detail->status_pengelolaan == 'Mandiri' ? 'selected' : '' }}>Mandiri
                                        </option>
                                        <option value="Perusahaan"
                                            {{ $detail->status_pengelolaan == 'Perusahaan' ? 'selected' : '' }}>Perusahaan
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nomor PBB</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_pbb]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_pbb }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jumlah PBB (Rp)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][jumlah_pbb]"
                                        class="form-control text-kecil" value="{{ $detail->jumlah_pbb }}">
                                </div>
                            </div>

                            {{-- Status & Tanggal --}}
                            <div class="row mt-2">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status Kepemilikan</label>
                                    <select name="lahan[{{ $index }}][status_kepemilikan]"
                                        class="form-select text-kecil choices-select">
                                        <option value="aktif"
                                            {{ $detail->status_kepemilikan == 'aktif' ? 'selected' : '' }}>
                                            Aktif</option>
                                        <option value="nonaktif"
                                            {{ $detail->status_kepemilikan == 'nonaktif' ? 'selected' : '' }}>Tidak Aktif
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="lahan[{{ $index }}][tanggal_mulai]"
                                        class="form-control text-kecil"
                                        value="{{ $detail->tanggal_mulai ? date('Y-m-d', strtotime($detail->tanggal_mulai)) : '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="lahan[{{ $index }}][tanggal_selesai]"
                                        class="form-control text-kecil"
                                        value="{{ $detail->tanggal_selesai ? date('Y-m-d', strtotime($detail->tanggal_selesai)) : '' }}">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Posisi Surat</label>
                                    <select name="lahan[{{ $index }}][posisi_surat]"
                                        class="form-select text-kecil choices-select">
                                        <option value="">Pilih Posisi Surat</option>
                                        <option value="Koperasi"
                                            {{ $detail->posisi_surat == 'Koperasi' ? 'selected' : '' }}>Koperasi
                                        </option>
                                        <option value="Notaris"
                                            {{ $detail->posisi_surat == 'Notaris' ? 'selected' : '' }}>Notaris</option>
                                        <option value="PTP" {{ $detail->posisi_surat == 'PTP' ? 'selected' : '' }}>
                                            PTP</option>
                                        <option value="Petani" {{ $detail->posisi_surat == 'Petani' ? 'selected' : '' }}>
                                            Petani</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Penyerahan Surat</label>
                                    <input type="text" name="lahan[{{ $index }}][status_penyerahan]"
                                        class="form-control text-kecil" value="{{ $detail->status_penyerahan }}">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Koordinat X</label>
                                    <input type="number" step="0.00000001"
                                        name="lahan[{{ $index }}][koordinat_x]" class="form-control text-kecil"
                                        value="{{ $detail->koordinat_x }}" placeholder="Contoh: 114.12345678">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Koordinat Y</label>
                                    <input type="number" step="0.00000001"
                                        name="lahan[{{ $index }}][koordinat_y]" class="form-control text-kecil"
                                        value="{{ $detail->koordinat_y }}" placeholder="Contoh: -3.12345678">
                                </div>
                            </div>
                            {{-- Upload file --}}
                            <div class="row mt-2">
                                {{-- FILE SHM --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Scan SHM</label>
                                    <input type="file" name="lahan[{{ $index }}][pdf_scan_shm]"
                                        class="form-control text-kecil" accept="application/pdf">

                                    {{-- Hidden input untuk menandai penghapusan SHM --}}
                                    <input type="hidden" name="lahan[{{ $index }}][hapus_shm]" class="hapus_shm"
                                        value="0">

                                    @if ($detail->pdf_scan_shm)
                                        <div class="file-shm-container mt-1 d-flex align-items-center gap-2">
                                            <small class="text-muted">
                                                File saat ini:
                                                <a href="{{ asset('storage/' . $detail->pdf_scan_shm) }}"
                                                    target="_blank">Lihat File</a>
                                            </small>
                                            {{-- Tombol hapus file SHM --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-shm"
                                                title="Hapus file SHM">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                {{-- FILE PETA --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Scan Peta</label>
                                    <input type="file" name="lahan[{{ $index }}][pdf_scan_peta]"
                                        class="form-control text-kecil" accept="application/pdf">

                                    {{-- Hidden input untuk menandai penghapusan Peta --}}
                                    <input type="hidden" name="lahan[{{ $index }}][hapus_peta]"
                                        class="hapus_peta" value="0">

                                    @if ($detail->pdf_scan_peta)
                                        <div class="file-peta-container mt-1 d-flex align-items-center gap-2">
                                            <small class="text-muted">
                                                File saat ini:
                                                <a href="{{ asset('storage/' . $detail->pdf_scan_peta) }}"
                                                    target="_blank">Lihat File</a>
                                            </small>
                                            {{-- Tombol hapus file Peta --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-peta"
                                                title="Hapus file Peta">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('kepemilikan.index', [
                    'page' => request('page'),
                    'search' => request('search'),
                    'desa' => request('desa'),
                    'tahun' => request('tahun'),
                    'status_pengelolaan' => request('status_pengelolaan'),
                    'status' => request('status'),
                ]) }}"
                    class="btn btn-danger">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- ======= MODAL GANTI KEPEMILIKAN ======= -->
    <div class="modal fade" id="modalGantiKepemilikan" tabindex="-1" aria-labelledby="gantiPemilikLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <form id="formGantiKepemilikan"
                action="{{ route('kepemilikan.updateKepemilikan', [
                    'id_kepemilikan' => $kepemilikan->id_kepemilikan,
                    'id_lahan' => 0, // akan diganti via JS
                ]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="id_lahan" id="id_lahan_modal">
                <input type="hidden" name="id_petani_lama" value="{{ $kepemilikan->id_petani }}">

                <div class="modal-content rounded-4">
                    <div class="modal-header bg-success text-white rounded-top-4">
                        <h5 class="modal-title" id="gantiPemilikLabel">Ganti Kepemilikan Lahan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        {{-- MODE PILIHAN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mode</label>
                            <select name="mode" class="form-select choices-modal" id="modeSelect" required>
                                <option value="" disabled selected hidden>Pilih Mode</option>
                                <option value="lama" selected>Gunakan Petani Lama</option>
                                <option value="baru">Tambah Petani Baru</option>
                            </select>
                        </div>

                        {{-- MODE: PETANI LAMA --}}
                        <div id="petaniLama" class="mb-3">
                            <label class="form-label">Pilih Petani Baru (dari Data Lama)</label>
                            <select id="selectPetaniLama" name="id_petani_baru" class="form-select choices-modal">
                                @foreach ($petani as $p)
                                    @if ($p->id_petani != $kepemilikan->id_petani)
                                        <option value="{{ $p->id_petani }}">{{ $p->nomor_anggota_plasma }}
                                            ({{ $p->nama }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                        </div>

                        {{-- MODE: PETANI BARU --}}
                        <div id="petaniBaru" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>NIK</label>
                                    <input type="text" name="NIK" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Telepon</label>
                                    <input type="text" name="no_telepon" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Nomor Plasma</label>
                                    <input type="text" name="nomor_anggota_plasma" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Nomor Koperasi</label>
                                    <input type="text" name="nomor_anggota_koperasi" class="form-control">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label>Alamat</label>
                                    <textarea name="alamat" class="form-control form-control-sm custom-textarea"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Scan KTP (PDF)</label>
                                    <input type="file" name="pdf_scan_ktp" class="form-control"
                                        accept="application/pdf">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Scan KK (PDF)</label>
                                    <input type="file" name="pdf_scan_kk" class="form-control"
                                        accept="application/pdf">
                                </div>
                            </div>
                        </div>

                        {{-- TANGGAL DAN KETERANGAN --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Ganti</label>
                                <input type="date" name="tanggal_ganti" class="form-control"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control form-control-sm custom-textarea"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ================== INIT CHOICES UNTUK SELECT ==================
            const allSelects = document.querySelectorAll('select.choices-select');
            allSelects.forEach(select => {
                if (!select.classList.contains('choices-initialized')) {
                    const searchEnabled = select.classList.contains('choices-search');
                    new Choices(select, {
                        searchEnabled: searchEnabled,
                        shouldSort: false,
                        itemSelectText: '',
                        allowHTML: true,
                        position: 'auto',
                        placeholderValue: select.dataset.placeholder || '',
                        searchPlaceholderValue: select.dataset.searchPlaceholder || ''
                    });
                    select.classList.add('choices-initialized');
                }
            });

            // ================== MODAL GANTI KEPEMILIKAN ==================
            window.setLahanId = function(id) {
                const inputLahan = document.getElementById('id_lahan_modal');
                const form = document.getElementById('formGantiKepemilikan');
                inputLahan.value = id;
                form.action =
                    "{{ route('kepemilikan.updateKepemilikan', ['id_kepemilikan' => $kepemilikan->id_kepemilikan, 'id_lahan' => ':id']) }}"
                    .replace(':id', id);
            };

            // ================== TOGGLE PETANI LAMA / BARU ==================
            const modeSelect = document.getElementById('modeSelect');
            const petaniLama = document.getElementById('petaniLama');
            const petaniBaru = document.getElementById('petaniBaru');

            function toggleMode(value) {
                petaniLama.style.display = (value === 'lama') ? 'block' : 'none';
                petaniBaru.style.display = (value === 'baru') ? 'block' : 'none';
            }

            // Default dan event listener
            modeSelect.value = 'lama';
            toggleMode('lama');
            modeSelect.addEventListener('change', function() {
                toggleMode(this.value);
            });

            // ================== INIT CHOICES UNTUK SELECT MODAL ==================
            const modalGantiKepemilikan = document.getElementById('modalGantiKepemilikan');
            if (modalGantiKepemilikan) {
                modalGantiKepemilikan.addEventListener('shown.bs.modal', function() {
                    const modalSelects = modalGantiKepemilikan.querySelectorAll('select.choices-modal');
                    modalSelects.forEach(select => {
                        if (select.choices) select.choices.destroy();
                        new Choices(select, {
                            searchEnabled: select.id === 'selectPetaniLama',
                            placeholder: true,
                            placeholderValue: 'Pilih Petani',
                            searchPlaceholderValue: 'Cari petani...',
                            shouldSort: false,
                            itemSelectText: '',
                            allowHTML: true
                        });
                    });
                });
            }

            // Hapus file SHM
            document.querySelectorAll('.btn-hapus-shm').forEach(btn => {
                btn.addEventListener('click', function() {
                    const container = this.closest('.file-shm-container');
                    const inputHidden = container.parentElement.querySelector('.hapus_shm');

                    Swal.fire({
                        title: "Yakin ingin menghapus file SHM?",
                        text: "File ini akan dihapus permanen dari sistem.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#198754",
                        cancelButtonColor: "#dc3545",
                        confirmButtonText: "Ya, hapus",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            inputHidden.value = 1;
                            container.remove();

                            Swal.fire({
                                title: "Berhasil!",
                                text: "File SHM telah ditandai untuk dihapus.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            });

            // Hapus file Peta
            document.querySelectorAll('.btn-hapus-peta').forEach(btn => {
                btn.addEventListener('click', function() {
                    const container = this.closest('.file-peta-container');
                    const inputHidden = container.parentElement.querySelector('.hapus_peta');

                    Swal.fire({
                        title: "Yakin ingin menghapus file Peta?",
                        text: "File ini akan dihapus permanen dari sistem.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#198754",
                        cancelButtonColor: "#dc3545",
                        confirmButtonText: "Ya, hapus",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            inputHidden.value = 1;
                            container.remove();

                            Swal.fire({
                                title: "Berhasil!",
                                text: "File Peta telah ditandai untuk dihapus.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection