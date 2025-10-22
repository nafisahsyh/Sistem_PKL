@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container">
        <h3>Tambah Data Kepemilikan</h3>
        <form action="{{ route('kepemilikan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- DATA PETANI --}}
            <div class="card p-3 mb-3 bg-light">
                <h5>Data Petani</h5>
                <div class="mb-3">
                    <label for="id_petani" class="form-label">Nomor Plasma</label>
                    <select id="id_petani" name="id_petani" class="form-control" onchange="tampilDataPetani()">
                        <option value="">-- Pilih Nomor Plasma --</option>
                        @foreach ($petani as $p)
                            <option value="{{ $p->id_petani }}" data-nama="{{ $p->nama }}" data-nik="{{ $p->NIK }}"
                                data-anggota="{{ $p->nomor_anggota_koperasi }}" data-alamat="{{ $p->alamat }}"
                                data-dokumen="{{ asset('storage/ktp_pdf/' . $p->pdf_scan_ktp) }}">
                                {{ $p->nomor_anggota_plasma }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label>No. Anggota Koperasi</label>
                    <input type="text" id="anggota" class="form-control" readonly>
                </div>
                <div class="mb-2">
                    <label>NIK</label>
                    <input type="text" id="nik" class="form-control" readonly>
                </div>
                <div class="mb-2">
                    <label>Nama</label>
                    <input type="text" id="nama" class="form-control" readonly>
                </div>
                <div class="mb-2">
                    <label>Alamat</label>
                    <textarea id="alamat" class="form-control" rows="2" readonly></textarea>
                </div>
                <div class="mb-2">
                    <label>Dokumen Petani</label><br>
                    <a id="dokumen_link" href="#" target="_blank" class="btn btn-sm btn-info text-white d-none">
                        Lihat Dokumen PDF
                    </a>
                </div>
            </div>

            {{-- DATA LAHAN + DETAIL KEPEMILIKAN --}}
            <div class="card p-3 mb-3 bg-light">
                <h5>Data Lahan & Detail Kepemilikan</h5>
                <div id="lahan-container">
                    <div class="lahan-item border p-3 mb-3 rounded bg-white">
                        <h6 class="text-success">Lahan 1</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label>Desa</label>
                                <select name="lahan[0][id_desa]" class="form-control">
                                    <option value="">-- Pilih Desa --</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}">{{ $d->desa }} ({{ $d->kecamatan->kecamatan }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Tahun Tanam</label>
                                <select name="lahan[0][id_tahun_tanam]" class="form-control">
                                    <option value="">-- Pilih Tahun --</option>
                                    @foreach ($tahun_tanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Luas Tanah (Peta)</label>
                                <input type="number" step="0.01" name="lahan[0][luas_peta]" class="form-control">
                            </div>
                        </div>

                        <hr>
                        <h6>Detail Kepemilikan Lahan Ini</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label>Nomor SHM</label>
                                <input type="text" name="lahan[0][nomor_SHM]" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Nomor Sporadik</label>
                                <input type="text" name="lahan[0][nomor_sporadik]" class="form-control">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Luas Surat (m²)</label>
                                <input type="number" step="0.01" name="lahan[0][luas_surat]" class="form-control">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Nomor PBB</label>
                                <input type="text" name="lahan[0][nomor_pbb]" class="form-control">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label>Jumlah PBB (Rp)</label>
                                <input type="number" step="0.01" name="lahan[0][jumlah_pbb]" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
            </div>

            {{-- STATUS KEPEMILIKAN --}}
            <div class="card p-3 mb-3 bg-light">
                <h5>Status Kepemilikan</h5>
                <div class="mb-2">
                    <label>Status</label>
                    <select name="status_kepemilikan" class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non-Aktif</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Data</button>
        </form>
    </div>

    <script>
        function tampilDataPetani() {
            const select = document.getElementById('id_petani');
            const opt = select.options[select.selectedIndex];
            document.getElementById('anggota').value = opt.getAttribute('data-anggota') || '';
            document.getElementById('nik').value = opt.getAttribute('data-nik') || '';
            document.getElementById('nama').value = opt.getAttribute('data-nama') || '';
            document.getElementById('alamat').value = opt.getAttribute('data-alamat') || '';

            const dokumenLink = document.getElementById('dokumen_link');
            const dokumenURL = opt.getAttribute('data-dokumen');
            if (dokumenURL) {
                dokumenLink.href = dokumenURL;
                dokumenLink.classList.remove('d-none');
            } else {
                dokumenLink.classList.add('d-none');
            }
        }

        let lahanIndex = 1;
        function tambahLahan() {
            const container = document.getElementById('lahan-container');
            const template = container.firstElementChild.cloneNode(true);

            template.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\d+/, lahanIndex);
                el.value = '';
            });

            template.querySelector('h6.text-success').innerText = 'Lahan ' + (lahanIndex + 1);
            container.appendChild(template);
            lahanIndex++;
        }
    </script>
@endsection