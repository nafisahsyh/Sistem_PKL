@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

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

        <form action="{{ route('kepemilikan.update', $kepemilikan->id_kepemilikan) }}" method="POST">
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
                            <option value="">-- Pilih Nomor Plasma --</option>
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

                    <div class="col-md-6 mb-3">
                        <label for="anggota" class="form-label">Nomor Anggota Koperasi</label>
                        <input type="text" id="anggota" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nomor_anggota_koperasi ?? '' }}" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" id="nik" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->NIK ?? '' }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" id="nama" class="form-control text-kecil"
                            value="{{ $kepemilikan->petani->nama ?? '' }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea id="alamat" class="form-control text-kecil" rows="1" readonly>{{ $kepemilikan->petani->alamat ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ===================== DATA LAHAN ===================== --}}
            <div class="card p-4 mb-4 shadow-sm rounded-3">
                <h5 class="text-brown mb-3">Data Lahan & Detail Kepemilikan</h5>

                <div id="lahan-container">
                    @foreach ($kepemilikan->detailKepemilikan as $index => $detail)
                        <div class="border rounded p-3 mb-4 bg-light">
                            <h6 class="text-brown mb-3">Lahan {{ $index + 1 }}</h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Desa</label>
                                    <select name="lahan[{{ $index }}][id_desa]" class="form-select text-kecil">
                                        <option value="">-- Pilih Desa --</option>
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
                                        <option value="">-- Pilih Tahun --</option>
                                        @foreach ($tahun_tanam as $t)
                                            <option value="{{ $t->id_tahun_tanam }}"
                                                {{ $detail->lahan->id_tahun_tanam == $t->id_tahun_tanam ? 'selected' : '' }}>
                                                {{ $t->tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Luas Tanah (Peta)</label>
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
                                    <label class="form-label">Nomor Sporadik</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_sporadik]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_sporadik }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nomor PBB</label>
                                    <input type="text" name="lahan[{{ $index }}][nomor_pbb]"
                                        class="form-control text-kecil" value="{{ $detail->nomor_pbb }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Luas Surat (m²)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][luas_surat]"
                                        class="form-control text-kecil" value="{{ $detail->luas_surat }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jumlah PBB (Rp)</label>
                                    <input type="number" step="0.01" name="lahan[{{ $index }}][jumlah_pbb]"
                                        class="form-control text-kecil" value="{{ $detail->jumlah_pbb }}">
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
                            <option value="aktif" {{ $kepemilikan->status_kepemilikan == 'aktif' ? 'selected' : '' }}>
                                Aktif</option>
                            <option value="nonaktif"
                                {{ $kepemilikan->status_kepemilikan == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control text-kecil"
                            value="{{ $kepemilikan->tanggal_mulai }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control text-kecil"
                            value="{{ $kepemilikan->tanggal_selesai }}">
                    </div>
                </div>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    <script>
        // tampilkan info petani otomatis
        function tampilDataPetani() {
            const select = document.getElementById('id_petani');
            const opt = select.options[select.selectedIndex];
            document.getElementById('anggota').value = opt.getAttribute('data-anggota') || '';
            document.getElementById('nik').value = opt.getAttribute('data-nik') || '';
            document.getElementById('nama').value = opt.getAttribute('data-nama') || '';
            document.getElementById('alamat').value = opt.getAttribute('data-alamat') || '';
        }

        // duplikasi lahan baru
        let lahanIndex = {{ count($kepemilikan->detailKepemilikan) }};

        function tambahLahan() {
            const container = document.getElementById('lahan-container');
            const first = container.firstElementChild.cloneNode(true);

            first.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\d+/, lahanIndex);
                el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            first.querySelector('h6.text-success').innerText = 'Lahan ' + (lahanIndex + 1);
            container.appendChild(first);
            lahanIndex++;
        }
    </script>
@endsection
