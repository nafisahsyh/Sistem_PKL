@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Data Petani</h4>

        <div class="card p-4 shadow-sm rounded-3">
            <form action="{{ route('petani.update', $petani->id_petani) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nomor_anggota_plasma" class="form-label">Nomor Anggota Plasma</label>
                        <input type="text" class="form-control text-kecil @error('nomor_anggota_plasma') is-invalid @enderror"
                            id="nomor_anggota_plasma" name="nomor_anggota_plasma"
                            value="{{ old('nomor_anggota_plasma', $petani->nomor_anggota_plasma) }}" required>
                        @error('nomor_anggota_plasma')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_anggota_koperasi" class="form-label">Nomor Anggota Koperasi</label>
                        <input type="text" class="form-control text-kecil @error('nomor_anggota_koperasi') is-invalid @enderror"
                            id="nomor_anggota_koperasi" name="nomor_anggota_koperasi"
                            value="{{ old('nomor_anggota_koperasi', $petani->nomor_anggota_koperasi) }}" required>
                        @error('nomor_anggota_koperasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="NIK" class="form-label">NIK</label>
                        <input type="text" class="form-control text-kecil @error('NIK') is-invalid @enderror"
                            id="NIK" name="NIK" value="{{ old('NIK', $petani->NIK) }}" maxlength="16" required>
                        @error('NIK')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Petani</label>
                        <input type="text" class="form-control text-kecil @error('nama') is-invalid @enderror"
                            id="nama" name="nama" value="{{ old('nama', $petani->nama) }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="3"
                        class="form-control text-kecil @error('alamat') is-invalid @enderror"
                        required>{{ old('alamat', $petani->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status"
                            class="form-select text-kecil @error('status') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status', $petani->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak_aktif" {{ old('status', $petani->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pdf_scan_ktp" class="form-label">File Scan KTP (PDF)</label>
                        <input type="file" name="pdf_scan_ktp" id="pdf_scan_ktp"
                            class="form-control text-kecil @error('pdf_scan_ktp') is-invalid @enderror"
                            accept="application/pdf">

                        @if ($petani->pdf_scan_ktp)
                            <small class="text-muted">
                                File saat ini:
                                <a href="{{ asset('storage/' . $petani->pdf_scan_ktp) }}" target="_blank">Lihat PDF</a>
                            </small>
                        @endif

                        @error('pdf_scan_ktp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Perbarui</button>
                    <a href="{{ route('petani.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
