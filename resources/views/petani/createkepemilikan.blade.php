@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('petani.index') }}" class="btn btn-success p-2 me-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>
            <h4 class="text-brown mb-0">Tambah Data Kepemilikan</h4>
        </div>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('petani.storeKepemilikan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_petani" value="{{ $petani->id_petani }}">

            {{-- DATA PETANI --}}
            <div class="card p-4 mb-4 shadow-sm rounded-4 border-0">
                <h5 class="text-green-custom mb-3">Data Petani</h5>

                <div class="p-4 bg-light rounded-3 border">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Status Petani</div>

                            @php
                                $status = strtolower($petani->status ?? '-');
                                $label = ucwords(str_replace('_', ' ', $status));

                                // Tentukan warna
                                $color = match ($status) {
                                    'aktif' => 'bg-success',
                                    'tidak_aktif' => 'bg-secondary',
                                    'berhenti' => 'bg-danger',
                                };
                            @endphp

                            <span class="badge {{ $color }} px-3 py-2">
                                {{ $label }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nomor Plasma</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $petani->nomor_anggota_plasma ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nomor Koperasi</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $petani->nomor_anggota_koperasi ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">NIK</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $petani->NIK ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nama Petani</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $petani->nama ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Nomor Telepon</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $petani->no_telepon ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-dark fw-semibold">Alamat</div>
                            <div class="fs-6 fw-medium text-dark">
                                {{ $petani->alamat ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="my-3" style="height: 1px; background: #e5e5e5;"></div>
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="text-dark fw-semibold">Scan KTP</div>
                            <div>
                                @if ($petani->pdf_scan_ktp)
                                    <a href="{{ asset('storage/ktp_pdf/' . $petani->pdf_scan_ktp) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary mt-1">
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
                                @if ($petani->pdf_scan_kk)
                                    <a href="{{ asset('storage/ktp_pdf/' . $petani->pdf_scan_kk) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary mt-1">
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
            {{-- DATA LAHAN --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-green-custom mb-3">Data Lahan & Detail Kepemilikan</h5>
                <div id="lahan-container">
                    {{-- Lahan 1 --}}
                    <div class="border rounded p-3 mb-4 bg-light lahan-item">
                        <h6 class="text-brown mb-3">Lahan 1</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Desa</label>
                                <select name="lahan[0][id_desa]" class="form-select text-kecil select-desa">
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}">{{ $d->desa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tahun Tanam</label>
                                <select name="lahan[0][id_tahun_tanam]" class="form-select text-kecil select-tahun">
                                    @foreach ($tahun_tanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Kode</label>
                                <input type="text" name="lahan[0][kode_lahan]" class="form-control text-kecil"
                                    min="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Nomor Kavling</label>
                                <input type="text" name="lahan[0][nomor_kavling]" class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor SHM</label>
                                <input type="text" name="lahan[0][nomor_SHM]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nama SHM</label>
                                <input type="text" name="lahan[0][nama_SHM]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Luas Sesuai Lapangan (M²)<span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="lahan[0][luas_peta]"
                                    class="form-control text-kecil" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor Sporadik</label>
                                <input type="text" name="lahan[0][nomor_sporadik]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nama Sporadik</label>
                                <input type="text" name="lahan[0][nama_sporadik]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Luas Sesuai Surat (M²)</label>
                                <input type="number" step="0.01" min="0" name="lahan[0][luas_surat]"
                                    class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Kelola</label>
                                <select name="lahan[0][status_pengelolaan]" class="form-select text-kecil select-status">
                                    <option value="">Pilih Status Kelola</option>
                                    <option value="KSM">KSM</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="Perusahaan">Perusahaan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor PBB</label>
                                <input type="text" name="lahan[0][nomor_pbb]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah PBB (Rp)</label>
                                <input type="number" step="0.01" name="lahan[0][jumlah_pbb]"
                                    class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Kepemilikan</label>
                                <select name="lahan[0][status_kepemilikan]" class="form-select text-kecil select-status">
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="lahan[0][tanggal_mulai]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="lahan[0][tanggal_selesai]" class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Posisi Surat</label>
                                <select name="lahan[0][posisi_surat]" class="form-select text-kecil select-status">
                                    <option value="">Pilih Posisi Surat</option>
                                    <option value="Koperasi">Koperasi</option>
                                    <option value="Notaris">Notaris</option>
                                    <option value="PTP">PTP</option>
                                    <option value="Petani">Petani</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Penyerahan Surat</label>
                                <input type="text" name="lahan[0][status_penyerahan]" class="form-control text-kecil"
                                    placeholder="Masukkan keterangan">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Koordinat X</label>
                                <input type="number" step="0.00000001" name="lahan[0][koordinat_x]"
                                    class="form-control text-kecil" placeholder="Contoh: 114.12345678">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Koordinat Y</label>
                                <input type="number" step="0.00000001" name="lahan[0][koordinat_y]"
                                    class="form-control text-kecil" placeholder="Contoh: -3.12345678">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scan SHM</label>
                                <input type="file" name="lahan[0][pdf_scan_shm]"
                                    class="form-control text-kecil @error('lahan.0.pdf_scan_shm') is-invalid @enderror"
                                    accept="application/pdf">
                                @error('lahan.0.pdf_scan_shm')
                                    <div class="text-danger fst-italic small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted-small">Jenis file diterima: PDF (maks. 30MB).</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scan Peta</label>
                                <input type="file" name="lahan[0][pdf_scan_peta]" class="form-control"
                                    class="form-control text-kecil @error('lahan.0.pdf_scan_peta') is-invalid @enderror"
                                    accept="application/pdf">
                                @error('lahan.0.pdf_scan_peta')
                                    <div class="text-danger fst-italic small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted-small">Jenis file diterima: PDF (maks. 10MB).</small>
                            </div>
                        </div>

                    </div>
                </div>

                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
            </div>

            {{-- Hidden filter --}}
            <input type="hidden" name="desa" value="{{ request('desa') }}">
            <input type="hidden" name="tahun" value="{{ request('tahun') }}">
            <input type="hidden" name="status_petani" value="{{ request('status_petani') }}">
            <input type="hidden" name="status_pengelolaan" value="{{ request('status_pengelolaan') }}">

            {{-- STATUS KEPEMILIKAN --}}
            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Simpan</button>
                <a href="{{ route('petani.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ===== Inisialisasi Choices dan simpan instance =====
            function initChoices(select, options = {}) {
                const c = new Choices(select, options);
                select.choicesInstance = c;
                return c;
            }

            // ===== Sinkronisasi Status Pengelolaan <-> Status Kepemilikan =====
            function syncStatusHandlers(context = document) {
                // Dari Status Pengelolaan → Kepemilikan
                context.querySelectorAll('select[name$="[status_pengelolaan]"]').forEach(pengelolaanSelect => {
                    pengelolaanSelect.addEventListener('change', function() {
                        const index = this.name.match(/\d+/)[0];
                        const kepemilikanSelect = context.querySelector(
                            `select[name="lahan[${index}][status_kepemilikan]"]`
                        );
                        if (!kepemilikanSelect) return;

                        // HANYA paksa jika Perusahaan
                        if (this.value === 'Perusahaan') {
                            if (kepemilikanSelect.choicesInstance) {
                                kepemilikanSelect.choicesInstance.setChoiceByValue('nonaktif');
                            } else {
                                kepemilikanSelect.value = 'nonaktif';
                            }
                        }
                    });
                });
            }


            // ===== Inisialisasi Choices untuk lahan awal =====
            document.querySelectorAll('.select-desa').forEach(s => initChoices(s, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
                placeholderValue: 'Pilih Desa',
                searchPlaceholderValue: 'Cari Desa...'
            }));
            document.querySelectorAll('.select-tahun').forEach(s => initChoices(s, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
                placeholderValue: 'Pilih Tahun Tanam',
                searchPlaceholderValue: 'Cari Tahun...'
            }));
            document.querySelectorAll('.select-status').forEach(s => initChoices(s, {
                searchEnabled: false,
                shouldSort: false,
                itemSelectText: ''
            }));

            syncStatusHandlers(document);

            // ===== Tambah Lahan Dinamis =====
            let lahanIndex = document.querySelectorAll('.lahan-item').length;
            const container = document.getElementById('lahan-container');

            window.tambahLahan = function() {
                const lahanDiv = document.createElement('div');
                lahanDiv.classList.add('border', 'rounded', 'p-3', 'mb-4', 'bg-light', 'lahan-item');

                lahanDiv.innerHTML = `
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-lahan">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        <h6 class="text-brown mb-3">Lahan ${lahanIndex + 1}</h6>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Desa</label>
                                <select name="lahan[${lahanIndex}][id_desa]" class="form-select text-kecil select-desa">
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}">{{ $d->desa }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tahun Tanam</label>
                                <select name="lahan[${lahanIndex}][id_tahun_tanam]" class="form-select text-kecil select-tahun">
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
                                <label class="form-label">Luas Sesuai Lapangan (M²)<span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="lahan[${lahanIndex}][luas_peta]" class="form-control text-kecil" required>
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
                                <label class="form-label">Luas Sesuai Surat (M²)</label>
                                <input type="number" step="0.01" min="0" name="lahan[${lahanIndex}][luas_surat]" class="form-control text-kecil">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Kelola</label>
                                <select name="lahan[${lahanIndex}][status_pengelolaan]" class="form-select text-kecil select-status">
                                    <option value="">Pilih Status Kelola</option>
                                    <option value="KSM">KSM</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="Perusahaan">Perusahaan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor PBB</label>
                                <input type="text" name="lahan[${lahanIndex}][nomor_pbb]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah PBB (Rp)</label>
                                <input type="number" step="0.01" name="lahan[${lahanIndex}][jumlah_pbb]" class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Kepemilikan</label>
                                <select name="lahan[${lahanIndex}][status_kepemilikan]" class="form-select text-kecil select-status">
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="lahan[${lahanIndex}][tanggal_mulai]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="lahan[${lahanIndex}][tanggal_selesai]" class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Posisi Surat</label>
                                <select name="lahan[${lahanIndex}][posisi_surat]" class="form-select text-kecil select-status">
                                    <option value="">Pilih Posisi Surat</option>
                                    <option value="Koperasi">Koperasi</option>
                                    <option value="Notaris">Notaris</option>
                                    <option value="PTP">PTP</option>
                                    <option value="Petani">Petani</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Penyerahan Surat</label>
                                <input type="text" name="lahan[${lahanIndex}][status_penyerahan]" class="form-control text-kecil"
                                    placeholder="Masukkan keterangan">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Koordinat X</label>
                                <input type="number" step="0.00000001" name="lahan[${lahanIndex}][koordinat_x]"
                                    class="form-control text-kecil" placeholder="Contoh: 114.12345678">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Koordinat Y</label>
                                <input type="number" step="0.00000001" name="lahan[${lahanIndex}][koordinat_y]"
                                    class="form-control text-kecil" placeholder="Contoh: -3.12345678">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scan SHM</label>
                                <input type="file"
                                    name="lahan[${lahanIndex}][pdf_scan_shm]"
                                    class="form-control pdf-input"
                                    data-label="SHM"
                                    data-max="30720"
                                    accept="application/pdf">
                                <small class="text-muted-small">Jenis file diterima: PDF (maks. 30MB).</small>                                    
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scan Peta</label>
                                <input type="file"
                                    name="lahan[${lahanIndex}][pdf_scan_peta]"
                                    class="form-control pdf-input"
                                    data-label="Peta"
                                    data-max="10240"
                                    accept="application/pdf">
                                <small class="text-muted-small">Jenis file diterima: PDF (maks. 10MB).</small>                                    
                            </div>
                        </div>
                        `;

                container.appendChild(lahanDiv);

                // Inisialisasi Choices untuk select baru
                lahanDiv.querySelectorAll('.select-desa').forEach(s => initChoices(s, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholderValue: 'Pilih Desa',
                    searchPlaceholderValue: 'Cari Desa...'
                }));
                lahanDiv.querySelectorAll('.select-tahun').forEach(s => initChoices(s, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholderValue: 'Pilih Tahun Tanam',
                    searchPlaceholderValue: 'Cari Tahun...'
                }));
                lahanDiv.querySelectorAll('.select-status').forEach(s => initChoices(s, {
                    searchEnabled: false,
                    shouldSort: false,
                    itemSelectText: ''
                }));

                syncStatusHandlers(lahanDiv);
                lahanIndex++;
            };

            // ===== Hapus Lahan =====
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

            // ===== Form Submit =====
            document.querySelector('form').addEventListener('submit', function() {
                document.querySelectorAll('.select-status, .select-desa, .select-tahun').forEach(select => {
                    if (select.choicesInstance) select.value = select.choicesInstance.getValue(
                        true);
                });
            });

        });
    </script>
@endsection
