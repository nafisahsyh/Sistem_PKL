@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container">
        <h3>Edit Data Kepemilikan</h3>

        <form action="{{ route('kepemilikan.update', $kepemilikan->id_kepemilikan) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ===================== DATA PETANI ===================== --}}
            <div class="card p-3 mb-3 bg-light">
                <h5>Data Petani</h5>

                <div class="mb-3">
                    <label for="id_petani" class="form-label">Nomor Plasma</label>
                    <select id="id_petani" name="id_petani" class="form-control" onchange="tampilDataPetani()">
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

                <div class="mb-2">
                    <label>No. Anggota Koperasi</label>
                    <input type="text" id="anggota" class="form-control"
                        value="{{ $kepemilikan->petani->nomor_anggota_koperasi ?? '' }}" readonly>
                </div>

                <div class="mb-2">
                    <label>NIK</label>
                    <input type="text" id="nik" class="form-control" value="{{ $kepemilikan->petani->NIK ?? '' }}"
                        readonly>
                </div>

                <div class="mb-2">
                    <label>Nama</label>
                    <input type="text" id="nama" class="form-control"
                        value="{{ $kepemilikan->petani->nama ?? '' }}" readonly>
                </div>

                <div class="mb-2">
                    <label>Alamat</label>
                    <textarea id="alamat" class="form-control" rows="2" readonly>{{ $kepemilikan->petani->alamat ?? '' }}</textarea>
                </div>
            </div>

            {{-- ===================== DATA LAHAN & DETAIL KEPEMILIKAN ===================== --}}
            <div class="card p-3 mb-3 bg-light">
                <h5>Data Lahan & Detail Kepemilikan</h5>

                <div id="lahan-container">
                    @foreach ($kepemilikan->detailKepemilikan as $index => $detail)
                        <div class="lahan-item border p-3 mb-3 rounded bg-white">
                            <h6 class="text-success">Lahan {{ $index + 1 }}</h6>

                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label>Desa</label>
                                    <select name="detail[{{ $index }}][id_desa]" class="form-control">
                                        <option value="">-- Pilih Desa --</option>
                                        @foreach ($desa as $d)
                                            <option value="{{ $d->id_desa }}"
                                                {{ $detail->lahan->id_desa == $d->id_desa ? 'selected' : '' }}>
                                                {{ $d->desa }} ({{ $d->kecamatan->kecamatan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Tahun Tanam</label>
                                    <select name="detail[{{ $index }}][id_tahun_tanam]" class="form-control">
                                        <option value="">-- Pilih Tahun --</option>
                                        @foreach ($tahun_tanam as $t)
                                            <option value="{{ $t->id_tahun_tanam }}"
                                                {{ $detail->lahan->id_tahun_tanam == $t->id_tahun_tanam ? 'selected' : '' }}>
                                                {{ $t->tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Luas Tanah (Peta)</label>
                                    <input type="number" step="0.01" name="detail[{{ $index }}][luas_peta]"
                                        class="form-control" value="{{ $detail->lahan->luas_peta }}">
                                </div>
                            </div>

                            <hr>
                            <h6>Detail Kepemilikan Lahan Ini</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label>Nomor SHM</label>
                                    <input type="text" name="detail[{{ $index }}][nomor_SHM]"
                                        class="form-control" value="{{ $detail->nomor_SHM }}">
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label>Nomor Sporadik</label>
                                    <input type="text" name="detail[{{ $index }}][nomor_sporadik]"
                                        class="form-control" value="{{ $detail->nomor_sporadik }}">
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Luas Surat (m²)</label>
                                    <input type="number" step="0.01" name="detail[{{ $index }}][luas_surat]"
                                        class="form-control" value="{{ $detail->luas_surat }}">
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Nomor PBB</label>
                                    <input type="text" name="detail[{{ $index }}][nomor_pbb]"
                                        class="form-control" value="{{ $detail->nomor_pbb }}">
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Jumlah PBB (Rp)</label>
                                    <input type="number" step="0.01" name="detail[{{ $index }}][jumlah_pbb]"
                                        class="form-control" value="{{ $detail->jumlah_pbb }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
            </div>

            {{-- ===================== STATUS KEPEMILIKAN ===================== --}}
            <div class="card p-3 mb-3 bg-light">
                <h5>Status Kepemilikan</h5>

                <div class="mb-2">
                    <label>Status</label>
                    <select name="status_kepemilikan" class="form-control">
                        <option value="aktif" {{ $kepemilikan->status_kepemilikan == 'aktif' ? 'selected' : '' }}>Aktif
                        </option>
                        <option value="nonaktif" {{ $kepemilikan->status_kepemilikan == 'nonaktif' ? 'selected' : '' }}>
                            Non-Aktif</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control"
                        value="{{ $kepemilikan->tanggal_mulai }}">
                </div>

                <div class="mb-2">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control"
                        value="{{ $kepemilikan->tanggal_selesai }}">
                </div>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('kecamatan.index') }}" class="btn btn-danger">Batal</a>
            </div>
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
        }

        let lahanIndex = {{ count($kepemilikan->detailKepemilikan) }};

        function tambahLahan() {
            const container = document.getElementById('lahan-container');
            const first = container.firstElementChild.cloneNode(true);

            first.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\d+/, lahanIndex);
                el.value = '';
            });

            first.querySelector('h6.text-success').innerText = 'Lahan ' + (lahanIndex + 1);
            container.appendChild(first);
            lahanIndex++;
        }
    </script>
@endsection
