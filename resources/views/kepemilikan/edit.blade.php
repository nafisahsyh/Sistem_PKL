@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Data Kepemilikan</h4>

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
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Anggota Plasma</label>
                        <select id="id_petani" name="id_petani" class="form-select text-kecil"
                            onchange="tampilDataPetani()">
                            <option value="" disabled hidden>Pilih Nomor Anggota Plasma</option>
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

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Anggota Koperasi</label>
                        <input type="text" id="anggota" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nomor_anggota_koperasi ?? '' }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" id="nik" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->NIK ?? '' }}" readonly>
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

                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-danger btn-hapus-lahan">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <h6 class="text-brown mb-3">Lahan {{ $index + 1 }}</h6>

                            {{-- Informasi dasar lahan --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Desa</label>
                                    <select name="lahan[{{ $index }}][id_desa]" class="form-select text-kecil">
                                        <option value="" disabled hidden>Pilih Desa</option>
                                        @foreach ($desa as $d)
                                            <option value="{{ $d->id_desa }}"
                                                {{ $detail->lahan->id_desa == $d->id_desa ? 'selected' : '' }}>
                                                {{ $d->desa }} ({{ $d->kecamatan->kecamatan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tahun Tanam</label>
                                    <select name="lahan[{{ $index }}][id_tahun_tanam]"
                                        class="form-select text-kecil">
                                        <option value="" disabled hidden>Pilih Tahun Tanam</option>
                                        @foreach ($tahun_tanam as $t)
                                            <option value="{{ $t->id_tahun_tanam }}"
                                                {{ $detail->lahan->id_tahun_tanam == $t->id_tahun_tanam ? 'selected' : '' }}>
                                                {{ $t->tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Luas Lahan Berdasarkan Peta</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][luas_peta]"
                                        class="form-control text-kecil" value="{{ $detail->lahan->luas_peta }}">
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
                                    <label class="form-label">Nomor Kavling</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_kavling]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_kavling }}">
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
                                    <label class="form-label">Nomor PBB</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_pbb]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_pbb }}">
                                </div>
                            </div>

                            {{-- Luas & PBB --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Luas Lahan Berdasarkan Surat (m²)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][luas_surat]"
                                        class="form-control text-kecil" value="{{ $detail->luas_surat }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jumlah PBB (Rp)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][jumlah_pbb]"
                                        class="form-control text-kecil" value="{{ $detail->jumlah_pbb }}">
                                </div>
                            </div>

                            {{-- Tambahan Status & Tanggal --}}
                            <div class="row mt-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status Kepemilikan</label>
                                    <select name="lahan[{{ $index }}][status_kepemilikan]"
                                        class="form-select text-kecil">
                                        <option value="aktif"
                                            {{ $detail->status_kepemilikan == 'aktif' ? 'selected' : '' }}>Aktif</option>
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
                                                target="_blank">Lihat PDF</a>
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
                                                target="_blank">Lihat PDF</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    {{-- ========== SCRIPT ========== --}}
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // === Inisialisasi dropdown (tanpa duplicate dari clone) ===
            function initChoices(context = document) {
                context.querySelectorAll('select.form-select').forEach(select => {
                    if (!select.dataset.choicesInitialized) {
                        new Choices(select, {
                            searchEnabled: false,
                            shouldSort: false,
                            itemSelectText: '',
                            allowHTML: true,
                            position: 'auto'
                        });
                        select.dataset.choicesInitialized = true;
                    }
                });
            }
            initChoices();

            // === Fungsi tampil data petani ===
            window.tampilDataPetani = function() {
                const select = document.getElementById('id_petani');
                const opt = select.options[select.selectedIndex];
                document.getElementById('anggota').value = opt?.dataset.anggota || '';
                document.getElementById('nik').value = opt?.dataset.nik || '';
                document.getElementById('nama').value = opt?.dataset.nama || '';
                document.getElementById('alamat').value = opt?.dataset.alamat || '';
            };

            // === Tambah / Hapus Lahan ===
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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Desa</label>
                    <select name="lahan[${lahanIndex}][id_desa]" class="form-select text-kecil">
                        <option value="" disabled selected hidden>Pilih Desa</option>
                        @foreach ($desa as $d)
                            <option value="{{ $d->id_desa }}">{{ $d->desa }} ({{ $d->kecamatan->kecamatan }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tahun Tanam</label>
                    <select name="lahan[${lahanIndex}][id_tahun_tanam]" class="form-select text-kecil">
                        <option value="" disabled selected hidden>Pilih Tahun Tanam</option>
                        @foreach ($tahun_tanam as $t)
                            <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luas Lahan Berdasarkan Peta</label>
                    <input type="number" step="0.01" name="lahan[${lahanIndex}][luas_peta]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor SHM</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_SHM]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Sesuai SHM</label>
                    <input type="text" name="lahan[${lahanIndex}][nama_SHM]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Kavling</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_kavling]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Sporadik</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_sporadik]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Sesuai Sporadik</label>
                    <input type="text" name="lahan[${lahanIndex}][nama_sporadik]" class="form-control text-kecil">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor PBB</label>
                    <input type="text" name="lahan[${lahanIndex}][nomor_pbb]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Luas Lahan Berdasarkan Surat (m²)</label>
                    <input type="number" step="0.01" name="lahan[${lahanIndex}][luas_surat]" class="form-control text-kecil">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah PBB (Rp)</label>
                    <input type="number" step="0.01" name="lahan[${lahanIndex}][jumlah_pbb]" class="form-control text-kecil">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status Kepemilikan</label>
                    <select name="lahan[${lahanIndex}][status_kepemilikan]" class="form-select text-kecil" required>
                        <option value="" disabled hidden>Pilih Status</option>
                        <option value="aktif" selected>Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="lahan[${lahanIndex}][tanggal_mulai]" 
                        class="form-control text-kecil" 
                        value="${new Date().toISOString().split('T')[0]}">
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
                initChoices(lahanBaru); // aktifkan dropdown baru
            };

            // === Hapus lahan ===
            container.addEventListener('click', e => {
                if (e.target.closest('.btn-hapus-lahan')) {
                    e.target.closest('.lahan-item').remove();

                    [...container.children].forEach((el, i) => {
                        el.querySelector('h6').innerText = 'Lahan ' + (i + 1);
                        el.querySelectorAll('input, select').forEach(input => {
                            input.name = input.name.replace(/\d+/, i);
                        });
                    });
                    lahanIndex = container.children.length;
                }
            });
        });
    </script>
@endsection
