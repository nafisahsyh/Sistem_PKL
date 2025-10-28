@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Data Kepemilikan (Per Lahan)</h4>

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
        <form action="{{ route('kepemilikan.update', $kepemilikan->id_kepemilikan) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="id_petani" value="{{ $kepemilikan->id_petani }}">

            {{-- ===================== DATA PETANI ===================== --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Petani</h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Anggota Plasma</label>
                        <input type="text" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nomor_anggota_plasma }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Anggota Koperasi</label>
                        <input type="text" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nomor_anggota_koperasi }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control text-kecil" value="{{ $kepemilikan->petani->NIK }}"
                            readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control text-kecil" value="{{ $kepemilikan->petani->nama }}"
                            readonly>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control text-kecil" rows="1" readonly>{{ $kepemilikan->petani->alamat }}</textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Dokumen KTP</label><br>
                    @if ($kepemilikan->petani->pdf_scan_ktp)
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
                    @if ($kepemilikan->petani->pdf_scan_kk)
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
                <h5 class="text-brown mb-3">Data Lahan</h5>

                <div class="border rounded p-3 mb-4 bg-light lahan-item">
                    <input type="hidden" name="lahan[0][id_detail_kepemilikan]"
                        value="{{ $selectedDetail->id_detail_kepemilikan }}">
                    <input type="hidden" name="lahan[0][id_lahan]" value="{{ $selectedDetail->lahan->id_lahan }}">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Desa</label>
                            <select name="lahan[0][id_desa]" class="form-select text-kecil">
                                <option value="" disabled hidden>Pilih Desa</option>
                                @foreach ($desa as $d)
                                    <option value="{{ $d->id_desa }}"
                                        {{ $selectedDetail->lahan->id_desa == $d->id_desa ? 'selected' : '' }}>
                                        {{ $d->desa }} ({{ $d->kecamatan->kecamatan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun Tanam</label>
                            <select name="lahan[0][id_tahun_tanam]" class="form-select text-kecil">
                                <option value="" disabled hidden>Pilih Tahun Tanam</option>
                                @foreach ($tahun_tanam as $t)
                                    <option value="{{ $t->id_tahun_tanam }}"
                                        {{ $selectedDetail->lahan->id_tahun_tanam == $t->id_tahun_tanam ? 'selected' : '' }}>
                                        {{ $t->tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Luas Lahan Berdasarkan Peta</label>
                            <input type="number" step="0.01" name="lahan[0][luas_peta]" class="form-control text-kecil"
                                value="{{ $selectedDetail->lahan->luas_peta }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor SHM</label>
                            <input type="text" name="lahan[0][nomor_SHM]" class="form-control text-kecil"
                                value="{{ $selectedDetail->nomor_SHM }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Sesuai SHM</label>
                            <input type="text" name="lahan[0][nama_SHM]" class="form-control text-kecil"
                                value="{{ $selectedDetail->nama_SHM }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Kavling</label>
                            <input type="text" name="lahan[0][nomor_kavling]" class="form-control text-kecil"
                                value="{{ $selectedDetail->nomor_kavling }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Sporadik</label>
                            <input type="text" name="lahan[0][nomor_sporadik]" class="form-control text-kecil"
                                value="{{ $selectedDetail->nomor_sporadik }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Sesuai Sporadik</label>
                            <input type="text" name="lahan[0][nama_sporadik]" class="form-control text-kecil"
                                value="{{ $selectedDetail->nama_sporadik }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor PBB</label>
                            <input type="text" name="lahan[0][nomor_pbb]" class="form-control text-kecil"
                                value="{{ $selectedDetail->nomor_pbb }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Luas Lahan Berdasarkan Surat (m²)</label>
                            <input type="number" step="0.01" name="lahan[0][luas_surat]"
                                class="form-control text-kecil" value="{{ $selectedDetail->luas_surat }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah PBB (Rp)</label>
                            <input type="number" step="0.01" name="lahan[0][jumlah_pbb]"
                                class="form-control text-kecil" value="{{ $selectedDetail->jumlah_pbb }}">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status Kepemilikan</label>
                            <select name="lahan[0][status_kepemilikan]" class="form-select text-kecil">
                                <option value="aktif"
                                    {{ $selectedDetail->status_kepemilikan == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif"
                                    {{ $selectedDetail->status_kepemilikan == 'nonaktif' ? 'selected' : '' }}>Nonaktif
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="lahan[0][tanggal_mulai]" class="form-control text-kecil"
                                value="{{ $selectedDetail->tanggal_mulai ? date('Y-m-d', strtotime($selectedDetail->tanggal_mulai)) : '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="lahan[0][tanggal_selesai]" class="form-control text-kecil"
                                value="{{ $selectedDetail->tanggal_selesai ? date('Y-m-d', strtotime($selectedDetail->tanggal_selesai)) : '' }}">
                        </div>
                    </div>

                    {{-- FILE UPLOAD --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">File Scan SHM (PDF)</label>
                            <input type="file" name="lahan[0][pdf_scan_shm]" class="form-control text-kecil"
                                accept="application/pdf">
                            @if ($selectedDetail->pdf_scan_shm)
                                <small class="text-muted">
                                    File saat ini:
                                    <a href="{{ asset('storage/' . $selectedDetail->pdf_scan_shm) }}"
                                        target="_blank">Lihat PDF</a>
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">File Scan Peta (PDF)</label>
                            <input type="file" name="lahan[0][pdf_scan_peta]" class="form-control text-kecil"
                                accept="application/pdf">
                            @if ($selectedDetail->pdf_scan_peta)
                                <small class="text-muted">
                                    File saat ini:
                                    <a href="{{ asset('storage/' . $selectedDetail->pdf_scan_peta) }}"
                                        target="_blank">Lihat PDF</a>
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Choices('select.form-select', {
                searchEnabled: false,
                shouldSort: false,
                itemSelectText: '',
                allowHTML: true
            });
        });
    </script>
@endsection
