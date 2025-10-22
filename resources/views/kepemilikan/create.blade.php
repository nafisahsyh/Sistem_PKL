@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
<div class="container-fluid px-4 mt-5">
    <h4 class="mt-4 text-brown">Tambah Data Kepemilikan</h4>

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

        {{-- DATA PETANI --}}
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
                                data-dokumen="{{ asset('storage/ktp_pdf/' . $p->pdf_scan_ktp) }}">
                                {{ $p->nomor_anggota_plasma }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Anggota Koperasi</label>
                    <input type="text" id="anggota" class="form-control text-kecil" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">NIK</label>
                    <input type="text" id="nik" class="form-control text-kecil" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" id="nama" class="form-control text-kecil" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea id="alamat" class="form-control text-kecil" rows="1" readonly></textarea>
                </div>
            </div>
            <div class="mb-3">
                <a id="dokumen_link" href="#" target="_blank" class="btn btn-sm btn-info text-white d-none">
                    Lihat Dokumen PDF
                </a>
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
                                <option value="">-- Pilih Desa --</option>
                                @foreach ($desa as $d)
                                    <option value="{{ $d->id_desa }}">{{ $d->desa }} ({{ $d->kecamatan->kecamatan }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun Tanam</label>
                            <select name="lahan[0][id_tahun_tanam]" class="form-select text-kecil">
                                <option value="">-- Pilih Tahun --</option>
                                @foreach ($tahun_tanam as $t)
                                    <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Luas Tanah (Peta)</label>
                            <input type="number" step="0.01" name="lahan[0][luas_peta]" class="form-control text-kecil">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor SHM</label>
                            <input type="text" name="lahan[0][nomor_SHM]" class="form-control text-kecil">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Sporadik</label>
                            <input type="text" name="lahan[0][nomor_sporadik]" class="form-control text-kecil">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor PBB</label>
                            <input type="text" name="lahan[0][nomor_pbb]" class="form-control text-kecil">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Luas Surat (m²)</label>
                            <input type="number" step="0.01" name="lahan[0][luas_surat]" class="form-control text-kecil">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah PBB (Rp)</label>
                            <input type="number" step="0.01" name="lahan[0][jumlah_pbb]" class="form-control text-kecil">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-success" onclick="tambahLahan()">+ Tambah Lahan</button>
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
                    <input type="date" name="tanggal_mulai" class="form-control text-kecil">
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

    // reset input/select
    template.querySelectorAll('input, select').forEach(el => {
        el.name = el.name.replace(/\d+/, lahanIndex);
        el.value = '';
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });

    // ubah judul lahan
    template.querySelector('h6.text-brown').innerText = 'Lahan ' + (lahanIndex + 1);

    // tambahkan tombol hapus
    const header = document.createElement('div');
    header.classList.add('d-flex', 'justify-content-end', 'mb-3');
    const btnHapus = document.createElement('button');
    btnHapus.type = 'button';
    btnHapus.className = 'btn btn-sm btn-danger';
    btnHapus.innerText = 'Hapus';
    btnHapus.onclick = function() { hapusLahan(this); };
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
@endsection
