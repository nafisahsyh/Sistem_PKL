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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('petani.storeKepemilikan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_petani" value="{{ $petani->id_petani }}">

            {{-- DATA PETANI --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Petani</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nomor Plasma</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->nomor_anggota_plasma }}"
                            readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nomor Koperasi</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->nomor_anggota_koperasi }}"
                            readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->NIK }}" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->no_telepon }}" readonly>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->nama }}" readonly>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control text-kecil" rows="1" readonly>{{ $petani->alamat }}</textarea>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Dokumen KTP</label><br>
                    @if ($petani->pdf_scan_ktp)
                        <a href="{{ asset('storage/ktp_pdf/' . $petani->pdf_scan_ktp) }}" target="_blank"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-pdf"></i> Dokumen
                        </a>
                    @else
                        <span class="text-muted">Tidak ada</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Dokumen Kartu Keluarga</label><br>
                    @if ($petani->pdf_scan_kk)
                        <a href="{{ asset('storage/ktp_pdf/' . $petani->pdf_scan_kk) }}" target="_blank"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-pdf"></i> Dokumen
                        </a>
                    @else
                        <span class="text-muted">Tidak ada</span>
                    @endif
                </div>
            </div>

            {{-- DATA LAHAN --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Lahan & Detail Kepemilikan</h5>
                <div id="lahan-container">
                    {{-- Lahan 1 --}}
                    <div class="border rounded p-3 mb-4 bg-light lahan-item">
                        <h6 class="text-brown mb-3">Lahan 1</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Desa</label>
                                <select name="lahan[0][id_desa]" class="form-select text-kecil select-desa">
                                    <option value="" disabled selected hidden>Pilih Desa</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}">{{ $d->desa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tahun Tanam</label>
                                <select name="lahan[0][id_tahun_tanam]" class="form-select text-kecil select-tahun">
                                    <option value="" disabled selected hidden>Pilih Tahun Tanam</option>
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
                                <label class="form-label">Luas Sesuai Lapangan (M²)</label>
                                <input type="number" step="0.01" name="lahan[0][luas_peta]"
                                    class="form-control text-kecil" min="0">
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
                                <input type="number" step="0.01" name="lahan[0][luas_surat]"
                                    class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor PBB</label>
                                <input type="text" name="lahan[0][nomor_pbb]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah PBB (Rp)</label>
                                <input type="number" step="0.01" name="lahan[0][jumlah_pbb]"
                                    class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status Kepemilikan</label>
                                <select name="lahan[0][status_kepemilikan]" class="form-select text-kecil select-status">
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="lahan[0][tanggal_mulai]" class="form-control text-kecil"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="lahan[0][tanggal_selesai]" class="form-control text-kecil">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PDF SHM</label>
                                <input type="file" name="lahan[0][pdf_scan_shm]" class="form-control"
                                    accept="application/pdf">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PDF Peta</label>
                                <input type="file" name="lahan[0][pdf_scan_peta]" class="form-control"
                                    accept="application/pdf">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
            </div>

            {{-- STATUS KEPEMILIKAN --}}
            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Simpan</button>
                <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Choices untuk select yang sudah ada
            document.querySelectorAll('.select-desa, .select-tahun, .select-status').forEach(select => {
                new Choices(select, {
                    searchEnabled: false,
                    shouldSort: false,
                    itemSelectText: ''
                });
            });

            // Tambah lahan dinamis
            let lahanIndex = 1; // karena Lahan 1 sudah ada
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
                    <option value="" disabled selected hidden>Pilih Desa</option>
                    @foreach ($desa as $d)
                        <option value="{{ $d->id_desa }}">{{ $d->desa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Tahun Tanam</label>
                <select name="lahan[${lahanIndex}][id_tahun_tanam]" class="form-select text-kecil select-tahun">
                    <option value="" disabled selected hidden>Pilih Tahun Tanam</option>
                    @foreach ($tahun_tanam as $t)
                        <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Kode</label>
                <input type="text" name="lahan[${lahanIndex}][kode_lahan]"
                    class="form-control text-kecil" min="0">
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
                <label class="form-label">Luas Sesuai Lapangan (M²)</label>
                <input type="number" step="0.01" name="lahan[${lahanIndex}][luas_peta]" class="form-control text-kecil" min="0">
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
                <input type="number" step="0.01" name="lahan[${lahanIndex}][luas_surat]" class="form-control text-kecil">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nomor PBB</label>
                <input type="text" name="lahan[${lahanIndex}][nomor_pbb]" class="form-control text-kecil">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Jumlah PBB (Rp)</label>
                <input type="number" step="0.01" name="lahan[${lahanIndex}][jumlah_pbb]" class="form-control text-kecil">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <label class="form-label">Status Kepemilikan</label>
                <select name="lahan[${lahanIndex}][status_kepemilikan]" class="form-select text-kecil">
                    <option value="aktif" selected>Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="lahan[${lahanIndex}][tanggal_mulai]" class="form-control text-kecil" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="lahan[${lahanIndex}][tanggal_selesai]" class="form-control text-kecil">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">PDF SHM</label>
                <input type="file" name="lahan[${lahanIndex}][pdf_scan_shm]" class="form-control" accept="application/pdf">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">PDF Peta</label>
                <input type="file" name="lahan[${lahanIndex}][pdf_scan_peta]" class="form-control" accept="application/pdf">
            </div>
        </div>
    `;
                container.appendChild(lahanDiv);

                // Inisialisasi Choices untuk select baru
                new Choices(lahanDiv.querySelector('.select-desa'), {
                    searchEnabled: false,
                    shouldSort: false,
                    itemSelectText: ''
                });
                new Choices(lahanDiv.querySelector('.select-tahun'), {
                    searchEnabled: false,
                    shouldSort: false,
                    itemSelectText: ''
                });

                lahanIndex++;
            };


            // Hapus lahan
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
