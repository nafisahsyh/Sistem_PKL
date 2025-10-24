@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
{{-- Tambahkan stylesheet Choices.js --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('petani.index') }}" class="btn btn-success p-2 me-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>
            <h4 class="text-brown mb-0">Tambah Data Kepemilikan</h4>
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

        <form action="{{ route('kepemilikan.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_petani" value="{{ $petani->id_petani }}">

            {{-- DATA PETANI --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Petani</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Anggota Plasma</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->nomor_anggota_plasma }}"
                            readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Anggota Koperasi</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->nomor_anggota_koperasi }}"
                            readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->NIK }}" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control text-kecil" value="{{ $petani->nama }}" readonly>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control text-kecil" rows="1" readonly>{{ $petani->alamat }}</textarea>
                    </div>
                </div>
                <div class="mb-3">
                    @if ($petani->pdf_scan_ktp)
                        <a href="{{ asset('storage/ktp_pdf/' . $petani->pdf_scan_ktp) }}" target="_blank"
                            class="btn btn-sm btn-info text-white">
                            Lihat Dokumen PDF
                        </a>
                    @else
                        <span class="text-muted">Tidak ada dokumen</span>
                    @endif
                </div>
                <div class="mb-3">
                    @if ($petani->pdf_scan_kk)
                        <a href="{{ asset('storage/ktp_pdf/' . $petani->pdf_scan_kk) }}" target="_blank"
                            class="btn btn-sm btn-info text-white">
                            Lihat Dokumen PDF
                        </a>
                    @else
                        <span class="text-muted">Tidak ada dokumen</span>
                    @endif
                </div>
            </div>

            {{-- DATA LAHAN & DETAIL KEPEMILIKAN --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Lahan & Detail Kepemilikan</h5>
                <div id="lahan-container">
                    <div class="border rounded p-3 mb-4 bg-light lahan-item">
                        <h6 class="text-brown mb-3">Lahan 1</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Desa</label>
                                <select name="lahan[0][id_desa]" class="form-select text-kecil">
                                    <option value="" disabled selected hidden>Pilih Desa</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}">{{ $d->desa }}
                                            ({{ $d->kecamatan->kecamatan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tahun Tanam</label>
                                <select name="lahan[0][id_tahun_tanam]" class="form-select text-kecil">
                                    <option value="" disabled selected hidden>Pilih Tahun Tanam</option>
                                    @foreach ($tahun_tanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Luas Lahan Berdasarkan Peta</label>
                                <input type="number" step="0.01" name="lahan[0][luas_peta]" class="form-control text-kecil"
                                    min="0">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor SHM</label>
                                <input type="text" name="lahan[0][nomor_SHM]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nama Sesuai SHM</label>
                                <input type="text" name="lahan[0][nama_SHM]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor Kavling</label>
                                <input type="text" name="lahan[0][nomor_kavling]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor Sporadik</label>
                                <input type="text" name="lahan[0][nomor_sporadik]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nama Sesuai Sporadik</label>
                                <input type="text" name="lahan[0][nama_sporadik]" class="form-control text-kecil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nomor PBB</label>
                                <input type="text" name="lahan[0][nomor_pbb]" class="form-control text-kecil">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Luas Lahan Berdasarkan Surat</label>
                                <input type="number" step="0" name="lahan[0][luas_surat]" class="form-control text-kecil"
                                    min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah PBB (Rp)</label>
                                <input type="number" step="0" name="lahan[0][jumlah_pbb]" class="form-control text-kecil"
                                    min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
            </div>

            {{-- inputan SHM dan peta --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">File SHM dan Peta</h5>

                <!-- Tambahan upload file SHM dan Peta -->
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label for="pdf_scan_SHM" class="form-label">File SHM (PDF)</label>
                        <input type="file" name="pdf_scan_SHM"
                            class="form-control text-kecil @error('pdf_scan_SHM') is-invalid @enderror"
                            accept="application/pdf">
                        @error('pdf_scan_SHM')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted-small">Hanya file PDF, maksimal 10MB.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pdf_scan_peta" class="form-label">File Peta (PDF)</label>
                        <input type="file" name="pdf_scan_peta"
                            class="form-control text-kecil @error('pdf_scan_peta') is-invalid @enderror"
                            accept="application/pdf">
                        @error('pdf_scan_peta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted-small">Hanya file PDF, maksimal 10MB.</small>
                    </div>
                </div>
            </div>


            {{-- STATUS KEPEMILIKAN --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Status Kepemilikan</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status_kepemilikan" class="form-select text-kecil">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control text-kecil" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control text-kecil">
                    </div>
                </div>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Simpan</button>
                <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    {{-- Script untuk tambah/hapus lahan --}}
    <script>
        let lahanIndex = 1;

        function tambahLahan() {
            const container = document.getElementById('lahan-container');
            const template = container.firstElementChild.cloneNode(true);

            template.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\d+/, lahanIndex);
                el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            template.querySelector('h6.text-brown').innerText = 'Lahan ' + (lahanIndex + 1);

            const header = document.createElement('div');
            header.classList.add('d-flex', 'justify-content-end', 'mb-3');
            const btnHapus = document.createElement('button');
            btnHapus.type = 'button';
            btnHapus.className = 'btn btn-sm btn-danger';
            btnHapus.innerHTML = '<i class="fa fa-trash-can"></i>';
            btnHapus.onclick = function () {
                hapusLahan(this);
            };
            header.appendChild(btnHapus);
            template.insertBefore(header, template.firstChild);

            container.appendChild(template);
            lahanIndex++;
        }

        function hapusLahan(btn) {
            const container = document.getElementById('lahan-container');
            btn.closest('.lahan-item').remove();

            [...container.children].forEach((el, i) => {
                el.querySelector('h6.text-brown').innerText = 'Lahan ' + (i + 1);
                el.querySelectorAll('input, select').forEach(input => {
                    input.name = input.name.replace(/\d+/, i);
                });
            });

            lahanIndex = container.children.length;
        }
    </script>

    {{-- Tambahkan script Choices.js untuk dropdown --}}
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('select.form-select').forEach(select => {
                new Choices(select, {
                    searchEnabled: false,
                    shouldSort: false,
                    itemSelectText: '',
                    allowHTML: true,
                    position: 'auto',
                    placeholder: true,
                    placeholderValue: 'Pilih Status',
                });
            });
        });
    </script>
@endsection