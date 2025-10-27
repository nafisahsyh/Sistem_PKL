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
    <form action="{{ route('kepemilikan.update', $kepemilikan->id_kepemilikan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ===================== DATA PETANI ===================== --}}
        <div class="card p-4 mb-4 shadow-sm rounded-3">
            <h5 class="text-brown mb-3">Data Petani</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Anggota Plasma</label>
                    <select id="id_petani" name="id_petani" class="form-select text-kecil" onchange="tampilDataPetani()">
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
                        {{-- tambahkan di sini --}}
                        <input type="hidden" name="lahan[{{ $index }}][id_detail_kepemilikan]" 
                            value="{{ $detail->id_detail_kepemilikan }}">
                        <input type="hidden" name="lahan[{{ $index }}][id_lahan]" 
                            value="{{ $detail->lahan->id_lahan }}">
                        {{-- sampai sini --}}
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-lahan">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        <h6 class="text-brown mb-3">Lahan {{ $index + 1 }}</h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Desa</label>
                                <select name="lahan[{{ $index }}][id_desa]" class="form-select text-kecil">
                                    <option value="" disabled selected hidden>Pilih Desa</option>
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
                                <select name="lahan[{{ $index }}][id_tahun_tanam]" class="form-select text-kecil">
                                    <option value="" disabled selected hidden>Pilih Tahun Tanam</option>
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

                        {{-- FILE UPLOAD --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">File Scan SHM (PDF)</label>
                                <input type="file" name="lahan[{{ $index }}][pdf_scan_shm]"
                                    class="form-control text-kecil @error('lahan.' . $index . '.pdf_scan_shm') is-invalid @enderror"
                                    accept="application/pdf">
                                @if ($detail->pdf_scan_shm)
                                    <small class="text-muted">
                                        File saat ini:
                                        <a href="{{ asset('storage/' . $detail->pdf_scan_shm) }}" target="_blank">Lihat PDF</a>
                                    </small>
                                @endif
                                @error('lahan.' . $index . '.pdf_scan_shm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">File Scan Peta (PDF)</label>
                                <input type="file" name="lahan[{{ $index }}][pdf_scan_peta]"
                                    class="form-control text-kecil @error('lahan.' . $index . '.pdf_scan_peta') is-invalid @enderror"
                                    accept="application/pdf">
                                @if ($detail->pdf_scan_peta)
                                    <small class="text-muted">
                                        File saat ini:
                                        <a href="{{ asset('storage/' . $detail->pdf_scan_peta) }}" target="_blank">Lihat PDF</a>
                                    </small>
                                @endif
                                @error('lahan.' . $index . '.pdf_scan_peta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
        </div>

        {{-- ===================== STATUS KEPEMILIKAN ===================== --}}
        <div class="card p-4 mb-4 shadow-sm rounded-3">
            <h5 class="text-brown mb-3">Status Kepemilikan</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_kepemilikan" class="form-select text-kecil">
                        <option value="aktif" {{ $kepemilikan->status_kepemilikan == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ $kepemilikan->status_kepemilikan == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control text-kecil" value="{{ $kepemilikan->tanggal_mulai }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control text-kecil" value="{{ $kepemilikan->tanggal_selesai }}">
                </div>
            </div>
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
    document.querySelectorAll('select.form-select').forEach(select => {
        new Choices(select, { searchEnabled: false, shouldSort: false, itemSelectText: '' });
    });

    window.tampilDataPetani = function() {
        const select = document.getElementById('id_petani');
        const opt = select.options[select.selectedIndex];
        document.getElementById('anggota').value = opt.dataset.anggota || '';
        document.getElementById('nik').value = opt.dataset.nik || '';
        document.getElementById('nama').value = opt.dataset.nama || '';
        document.getElementById('alamat').value = opt.dataset.alamat || '';
    };

    // Tambah dan hapus lahan
    let lahanIndex = {{ count($kepemilikan->detailKepemilikan) }};
    const container = document.getElementById('lahan-container');

    window.tambahLahan = function() {
        const template = container.firstElementChild.cloneNode(true);
        template.querySelectorAll('input, select').forEach(el => {
            el.name = el.name.replace(/\d+/, lahanIndex);
            el.value = '';
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
        });
        template.querySelector('h6').innerText = 'Lahan ' + (lahanIndex + 1);
        container.appendChild(template);
        lahanIndex++;
    };

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
