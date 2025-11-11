@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@section('content')
    <div class="container-fluid px-4 mt-5">
        {{-- button back --}}
        <div class="d-flex align-items-center mb-3">
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
            <h4 class="text-brown mb-0 ms-3">Edit Data Kepemilikan</h4>
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

        {{-- ==== FORM EDIT KEPEMILIKAN ==== --}}
        <form action="{{ route('kepemilikan.update', $kepemilikan->id_kepemilikan) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ===================== DATA PETANI ===================== --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Petani</h5>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nomor Plasma</label>
                        <select id="id_petani" name="id_petani" class="form-select text-kecil choices-select"
                            onchange="tampilDataPetani()">
                            <option value="" disabled hidden>Pilih Nomor Plasma</option>
                            @foreach ($petani as $p)
                                <option value="{{ $p->id_petani }}" data-nama="{{ $p->nama }}"
                                    data-nik="{{ $p->NIK }}" data-anggota="{{ $p->nomor_anggota_koperasi }}"
                                    data-alamat="{{ $p->alamat }}"
                                    {{ $kepemilikan->id_petani == $p->id_petani ? 'selected' : '' }}>
                                    {{ $p->nomor_anggota_plasma }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nomor Koperasi</label>
                        <input type="text" id="anggota" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nomor_anggota_koperasi ?? '' }}" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" id="nik" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->NIK ?? '' }}" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" id="no_telepon" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->no_telepon ?? '' }}" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" id="nama" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nama ?? '' }}" readonly>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea id="alamat" class="form-control text-kecil" rows="1" readonly>{{ $kepemilikan->petani->alamat ?? '' }}</textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Dokumen KTP</label><br>
                    @if ($kepemilikan->petani->pdf_scan_ktp ?? false)
                        <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}" target="_blank"
                            class="btn btn-sm btn-info text-white">
                            <i class="fas fa-file-pdf"></i> Lihat
                        </a>
                    @else
                        <span class="text-muted">Tidak ada dokumen</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Dokumen KK</label><br>
                    @if ($kepemilikan->petani->pdf_scan_kk ?? false)
                        <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_kk) }}" target="_blank"
                            class="btn btn-sm btn-info text-white">
                            <i class="fas fa-file-pdf"></i> Lihat
                        </a>
                    @else
                        <span class="text-muted">Tidak ada dokumen</span>
                    @endif
                </div>
            </div>

            <!-- BUTTON GANTI KEPEMILIKAN SEMUA LAHAN -->
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                    data-bs-target="#modalGantiKepemilikanSemua">
                    <i class="fas fa-sync-alt"></i> Ganti Kepemilikan Semua Lahan
                </button>
            </div>

            {{-- ===================== DATA LAHAN ===================== --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Lahan & Detail Kepemilikan</h5>

                <div id="lahan-container">
                    @foreach ($kepemilikan->detailKepemilikan as $index => $detail)
                        <div class="border rounded p-3 mb-4 bg-light lahan-item">
                            {{-- Hidden ID --}}
                            <input type="hidden" name="lahan[{{ $index }}][id_detail_kepemilikan]"
                                value="{{ $detail->id_detail_kepemilikan }}">
                            <input type="hidden" name="lahan[{{ $index }}][id_lahan]"
                                value="{{ $detail->lahan->id_lahan }}">

                            <input type="hidden" name="lahan[{{ $index }}][hapus]" value="0">

                            {{-- Tombol hapus & ganti kepemilikan --}}
                            <div class="d-flex justify-content-end mb-2">
                                @if (count($kepemilikan->detailKepemilikan) > 1)
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-lahan me-2">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-warning btn-ganti-lahan"
                                    data-bs-toggle="modal" data-bs-target="#modalGantiKepemilikan"
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
                                    <input type="text" step="0.01" name="lahan[{{ $index }}][kode_lahan]"
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
                                    <label class="form-label">Luas Sesuai Lapangan (M²)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][luas_peta]"
                                        class="form-control text-kecil" value="{{ $detail->lahan->luas_peta }}">
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
                                    <!-- Status Kelola (tidak ada search) -->
                                    <select name="lahan[{{ $index }}][status_pengelolaan]"
                                        class="form-select text-kecil choices-select" required>
                                        <option value="KSM"
                                            {{ $detail->status_pengelolaan == 'KSM' || is_null($detail->status_pengelolaan) ? 'selected' : '' }}>
                                            KSM
                                        </option>
                                        <option value="Mandiri"
                                            {{ $detail->status_pengelolaan == 'Mandiri' ? 'selected' : '' }}>Mandiri
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
                            <div class="row mt-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status Kepemilikan</label>
                                    <select name="lahan[{{ $index }}][status_kepemilikan]"
                                        class="form-select text-kecil choices-select">
                                        <option value="aktif"
                                            {{ $detail->status_kepemilikan == 'aktif' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="nonaktif"
                                            {{ $detail->status_kepemilikan == 'nonaktif' ? 'selected' : '' }}>Nonaktif
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

                            {{-- Upload file --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">File Scan SHM (PDF)</label>
                                    <input type="file" name="lahan[{{ $index }}][pdf_scan_shm]"
                                        class="form-control text-kecil" accept="application/pdf">
                                    @if ($detail->pdf_scan_shm)
                                        <small class="text-muted">File saat ini:
                                            <a href="{{ asset('storage/' . $detail->pdf_scan_shm) }}"
                                                target="_blank">Lihat
                                                PDF</a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">File Scan Peta (PDF)</label>
                                    <input type="file" name="lahan[{{ $index }}][pdf_scan_peta]"
                                        class="form-control text-kecil" accept="application/pdf">
                                    @if ($detail->pdf_scan_peta)
                                        <small class="text-muted">File saat ini:
                                            <a href="{{ asset('storage/' . $detail->pdf_scan_peta) }}"
                                                target="_blank">Lihat
                                                PDF</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>


                {{-- === Tambahkan hidden input untuk page & filter === --}}
                <input type="hidden" name="page" value="{{ request('page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="desa" value="{{ request('desa') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                {{-- Tombol Perbarui / Batal --}}
                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Perbarui</button>
                    <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </div>
        </form>

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

        <!-- ======= MODAL GANTI KEPEMILIKAN SEMUA LAHAN (RAPI SAMA PERSIS) ======= -->
        <div class="modal fade" id="modalGantiKepemilikanSemua" tabindex="-1"
            aria-labelledby="modalGantiKepemilikanSemuaLabel" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <form action="{{ route('kepemilikan.updateKepemilikanSemua', $kepemilikan->id_kepemilikan) }}"
                    method="POST" enctype="multipart/form-data">

                    @csrf

                    <div class="modal-content rounded-4">

                        <!-- HEADER -->
                        <div class="modal-header bg-success text-white rounded-top-4">
                            <h5 class="modal-title" id="modalGantiKepemilikanSemuaLabel">
                                Ganti Kepemilikan Semua Lahan
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <!-- BODY -->
                        <div class="modal-body px-4 py-3">

                            {{-- MODE PILIH --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mode</label>
                                <select name="mode" class="form-select choices-modal" id="modeSelectSemua" required>
                                    <option value="" disabled selected hidden>Pilih Mode</option>
                                    <option value="lama" selected>Gunakan Petani Lama</option>
                                    <option value="baru">Tambah Petani Baru</option>
                                </select>
                            </div>

                            {{-- PILIH PETANI LAMA --}}
                            <div id="petaniLamaSemua" class="mb-3">
                                <label class="form-label">Pilih Petani Pengganti</label>
                                <select name="id_petani_baru" class="form-select choices-modal">
                                    @foreach ($petani as $p)
                                        @if ($p->id_petani != $kepemilikan->id_petani)
                                            <option value="{{ $p->id_petani }}">
                                                {{ $p->nomor_anggota_plasma }} ({{ $p->nama }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- MODE PETANI BARU (STRUKTUR SAMA PERSIS DENGAN MODAL PERTAMA) --}}
                            <div id="petaniBaruSemua" style="display: none;">
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

                            {{-- TANGGAL + KETERANGAN --}}
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

                        <!-- FOOTER -->
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Simpan Perubahan
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>


        <!-- SCRIPT MODE SWITCH -->
        <script>
            // ================== MODAL GANTI KEPEMILIKAN SEMUA ==================
            document.addEventListener('DOMContentLoaded', function() {

                // ================== TOGGLE PETANI LAMA / BARU ==================
                const modeSelectSemua = document.getElementById('modeSelectSemua');
                const petaniLamaSemua = document.getElementById('petaniLamaSemua');
                const petaniBaruSemua = document.getElementById('petaniBaruSemua');

                function toggleModeSemua(value) {
                    petaniLamaSemua.style.display = (value === 'lama') ? 'block' : 'none';
                    petaniBaruSemua.style.display = (value === 'baru') ? 'block' : 'none';
                }

                // ========== DEFAULT MODE: lama ==========
                if (modeSelectSemua) {
                    modeSelectSemua.value = 'lama';
                    toggleModeSemua('lama');

                    modeSelectSemua.addEventListener('change', function() {
                        toggleModeSemua(this.value);
                    });
                }

                // ================== INIT CHOICES.JS UNTUK MODAL SEMUA ==================
                const modalGantiSemua = document.getElementById('modalGantiKepemilikanSemua');

                if (modalGantiSemua) {
                    modalGantiSemua.addEventListener('shown.bs.modal', function() {

                        const modalSelects = modalGantiSemua.querySelectorAll('select.choices-modal');

                        modalSelects.forEach(select => {

                            // Hapus instance choices lama
                            if (select.choicesInstance) {
                                select.choicesInstance.destroy();
                            }

                            // *** ATURAN BARU ***
                            const isModeSelect = select.id === 'modeSelectSemua';

                            const choices = new Choices(select, {
                                searchEnabled: !isModeSelect,
                                // MODE dropdown TIDAK BISA search
                                // Petani Lama tetap BISA search
                                placeholder: true,
                                placeholderValue: 'Pilih Petani',
                                searchPlaceholderValue: 'Cari...',
                                shouldSort: false,
                                itemSelectText: '',
                                allowHTML: true
                            });

                            select.choicesInstance = choices;
                        });
                    });
                }

            });
        </script>


        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ===== Choices untuk LAHAN UTAMA =====
                function initChoices(context = document) {
                    // Semua select
                    context.querySelectorAll('select.choices-select').forEach(select => {
                        if (!select.classList.contains('choices-main-initialized')) {

                            // Hanya aktifkan search jika ada class 'choices-search'
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

                            select.classList.add('choices-main-initialized');
                        }
                    });
                }

                initChoices(document);


                // ================== TAMBAH / HAPUS LAHAN ==================
                let lahanIndex = {{ count($kepemilikan->detailKepemilikan) }};
                const container = document.getElementById('lahan-container');

                window.tambahLahan = function() {
                    const lahanBaru = document.createElement('div');
                    lahanBaru.classList.add('border', 'rounded', 'p-3', 'mb-4', 'bg-light', 'lahan-item');
                    lahanBaru.innerHTML = `
            <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-sm btn-danger btn-hapus-lahan">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            <h6 class="text-brown mb-3">Lahan ${lahanIndex + 1}</h6>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Desa</label>
                    <select name="lahan[${lahanIndex}][id_desa]" class="form-select text-kecil choices-select choices-search"
                        data-placeholder="Pilih Desa" data-search-placeholder="Cari Desa...">
                        @foreach ($desa as $d)
                            <option value="{{ $d->id_desa }}">{{ $d->desa }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun Tanam</label>
                    <select name="lahan[${lahanIndex}][id_tahun_tanam]" class="form-select text-kecil choices-select choices-search"
                        data-placeholder="Pilih Tahun Tanam" data-search-placeholder="Cari Tahun Tanam...">
                        @foreach ($tahun_tanam as $t)
                            <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kode</label>
                    <input type="text" name="lahan[${lahanIndex}][kode_lahan]" class="form-control text-kecil">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor Kavling</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_kavling]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor SHM</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_SHM]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama SHM</label>
                    <input type="text" name="lahan[${lahanIndex}][nama_SHM]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luas Lapangan (M²)</label>
                    <input type="number" step="0.01" name="lahan[${lahanIndex}][luas_peta]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Sporadik</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_sporadik]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Sporadik</label>
                    <input type="text" name="lahan[${lahanIndex}][nama_sporadik]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luas Surat (M²)</label>
                    <input type="number" step="0.01" name="lahan[${lahanIndex}][luas_surat]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status Kelola</label>
                    <select name="lahan[${lahanIndex}][status_pengelolaan]" class="form-select text-kecil choices-select">
                        <option value="KSM" selected>KSM</option>                    
                        <option value="Mandiri">Mandiri</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor PBB</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_pbb]" class="form-control text-kecil">
                </div>        
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah PBB</label>
                    <input type="text" name="lahan[${lahanIndex}][jumlah_pbb]" class="form-control text-kecil">
                </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status Kepemilikan</label>
                    <select name="lahan[${lahanIndex}][status_kepemilikan]" class="form-select text-kecil choices-select">
                        <option value="aktif" selected>Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="lahan[${lahanIndex}][tanggal_mulai]" class="form-control text-kecil" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="lahan[${lahanIndex}][tanggal_selesai]" class="form-control text-kecil">
                </div>
            </div>                                                                     
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">File Scan SHM (PDF)</label>
                    <input type="file" name="lahan[${lahanIndex}][pdf_scan_shm]" accept="application/pdf" class="form-control text-kecil">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">File Scan Peta (PDF)</label>
                    <input type="file" name="lahan[${lahanIndex}][pdf_scan_peta]" accept="application/pdf" class="form-control text-kecil">
                </div>
            </div>
        `;
                    container.appendChild(lahanBaru);
                    lahanIndex++;
                    initChoices(lahanBaru); // init select Choices di lahan baru
                    updateHapusTombol();
                };

                container.addEventListener('click', function(e) {
                    const btn = e.target.closest('.btn-hapus-lahan');
                    if (!btn) return;

                    const item = btn.closest('.lahan-item');

                    // Tandai hidden input 'hapus' jadi 1
                    const hapusInput = item.querySelector('input[name*="[hapus]"]');
                    if (hapusInput) hapusInput.value = 1;

                    // Sembunyikan dari user
                    item.style.display = 'none';

                    // Update index & tombol hapus seperti biasa
                    updateIndices();
                    updateHapusTombol();
                });

                function updateIndices() {
                    container.querySelectorAll('.lahan-item').forEach((item, index) => {
                        item.querySelectorAll('input, select').forEach(input => {
                            input.name = input.name.replace(/lahan\[\d+\]/, `lahan[${index}]`);
                        });
                        item.querySelector('h6').textContent = `Lahan ${index + 1}`;
                    });
                    lahanIndex = container.querySelectorAll('.lahan-item').length;
                }

                function updateHapusTombol() {
                    const items = container.querySelectorAll('.lahan-item');
                    items.forEach(item => {
                        const btn = item.querySelector('.btn-hapus-lahan');
                        btn.style.display = (items.length <= 1) ? 'none' : 'inline-block';
                    });
                }

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
                        const modalSelects = modalGantiKepemilikan.querySelectorAll(
                            'select.choices-modal'
                        );
                        modalSelects.forEach(select => {
                            // destroy instance lama kalau ada
                            if (select.choices) {
                                select.choices.destroy();
                            }

                            new Choices(select, {
                                searchEnabled: select.id ===
                                    'selectPetaniLama', // cuma search untuk petani lama
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


            });
        </script>

    @endsection
