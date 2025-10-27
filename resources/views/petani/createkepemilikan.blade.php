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
                    <div class="border rounded p-3 mb-4 bg-light lahan-item">
                        <h6 class="text-brown mb-3">Lahan 1</h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Desa</label>
                                <select name="lahan[0][id_desa]" class="form-select text-kecil">
                                    <option value="" disabled selected hidden>Pilih Desa</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}">{{ $d->desa }} ({{ $d->kecamatan->kecamatan }})
                                        </option>
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
                                <label class="form-label">Luas Lahan Berdasarkan Peta (m²)</label>
                                <input type="number" step="0.01" name="lahan[0][luas_peta]" class="form-control text-kecil"
                                    min="0">
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
                                <label class="form-label">Nomor Kavling</label>
                                <input type="text" name="lahan[0][nomor_kavling]" class="form-control text-kecil">
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
                                <label class="form-label">Nomor PBB</label>
                                <input type="text" name="lahan[0][nomor_pbb]" class="form-control text-kecil">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Luas Lahan Berdasarkan Surat (m²)</label>
                                <input type="number" step="0.01" name="lahan[0][luas_surat]"
                                    class="form-control text-kecil">
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
                                <select name="lahan[0][status_kepemilikan]" class="form-select text-kecil">
                                    <option value="aktif" {{ isset($lahanData['status_kepemilikan']) && $lahanData['status_kepemilikan'] == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ isset($lahanData['status_kepemilikan']) && $lahanData['status_kepemilikan'] == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
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
        let lahanIndex = 1;

        const desaOptions = `@foreach($desa as $d)
            <option value="{{ $d->id_desa }}">{{ $d->desa }} ({{ $d->kecamatan->kecamatan }})</option>
        @endforeach`;

        const tahunTanamOptions = `@foreach($tahun_tanam as $t)
            <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
        @endforeach`;

        function tambahLahan() {
            const container = document.getElementById('lahan-container');
            const template = container.firstElementChild.cloneNode(true);

            // Reset input & file
            template.querySelectorAll('input').forEach(el => el.value = '');
            template.querySelectorAll('input[type="file"]').forEach(el => el.value = '');

            // Reset select
            const selectDesa = template.querySelector('select[name$="[id_desa]"]');
            selectDesa.innerHTML = `<option value="" disabled selected hidden>Pilih Desa</option>` + desaOptions;

            const selectTahun = template.querySelector('select[name$="[id_tahun_tanam]"]');
            selectTahun.innerHTML = `<option value="" disabled selected hidden>Pilih Tahun Tanam</option>` + tahunTanamOptions;

            // Update index
            template.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${lahanIndex}]`));
            });

            // Update heading
            template.querySelector('h6.text-brown').innerText = 'Lahan ' + (lahanIndex + 1);

            // Tombol hapus
            const header = document.createElement('div');
            header.classList.add('d-flex', 'justify-content-end', 'mb-3');
            const btnHapus = document.createElement('button');
            btnHapus.type = 'button';
            btnHapus.className = 'btn btn-sm btn-danger';
            btnHapus.innerHTML = '<i class="fa fa-trash-can"></i>';
            btnHapus.onclick = function () { hapusLahan(this); };
            header.appendChild(btnHapus);
            template.insertBefore(header, template.firstChild);

            container.appendChild(template);

            // Inisialisasi Choices.js baru untuk select baru
            new Choices(selectDesa, { searchEnabled: false, shouldSort: false, itemSelectText: '' });
            new Choices(selectTahun, { searchEnabled: false, shouldSort: false, itemSelectText: '' });

            lahanIndex++;
        }

        function hapusLahan(btn) {
            const container = document.getElementById('lahan-container');
            btn.closest('.lahan-item').remove();
            [...container.children].forEach((el, i) => {
                el.querySelector('h6.text-brown').innerText = 'Lahan ' + (i + 1);
                el.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) input.setAttribute('name', name.replace(/\[\d+\]/, `[${i}]`));
                });
            });
            lahanIndex = container.children.length;
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('select.form-select').forEach(select => {
                new Choices(select, { searchEnabled: false, shouldSort: false, itemSelectText: '' });
            });
        });
    </script>
@endsection