@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Tambah Petani</h4>

        <div class="card p-4">
            <form action="{{ route('petani.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nomor_anggota_plasma" class="form-label">Nomor Plasma</label>
                        <input type="text" name="nomor_anggota_plasma"
                            class="form-control text-kecil @error('nomor_anggota_plasma') is-invalid @enderror"
                            value="{{ old('nomor_anggota_plasma') }}" placeholder="Masukkan nomor anggota plasma">
                        @error('nomor_anggota_plasma')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_anggota_koperasi" class="form-label">Nomor Koperasi</label>
                        <input type="text" name="nomor_anggota_koperasi"
                            class="form-control text-kecil @error('nomor_anggota_koperasi') is-invalid @enderror"
                            value="{{ old('nomor_anggota_koperasi') }}" placeholder="Masukkan nomor anggota koperasi">
                        @error('nomor_anggota_koperasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="NIK" class="form-label">NIK</label>
                        <input type="text" name="NIK"
                            class="form-control text-kecil @error('NIK') is-invalid @enderror" value="{{ old('NIK') }}"
                            placeholder="Masukkan NIK 16 digit" maxlength="16">
                        @error('NIK')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama"
                            class="form-control text-kecil @error('nama') is-invalid @enderror" value="{{ old('nama') }}"
                            placeholder="Masukkan nama lengkap" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control text-kecil @error('alamat') is-invalid @enderror"
                        placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="no_telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control text-kecil @error('no_telepon') is-invalid @enderror"
                            id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}"
                            placeholder="08xxxx atau +628xxxx">
                        @error('no_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status"
                            class="form-select text-kecil @error('status') is-invalid @enderror" required>
                            <option value="aktif" selected>Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                            <option value="berhenti">Berhenti</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pdf_scan_ktp" class="form-label">Scan KTP (PDF)</label>
                        <input type="file" name="pdf_scan_ktp"
                            class="form-control text-kecil @error('pdf_scan_ktp') is-invalid @enderror"
                            accept="application/pdf">
                        @error('pdf_scan_ktp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted-small">Hanya file PDF, maksimal 10MB.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pdf_scan_ktp" class="form-label">Scan KK (PDF)</label>
                        <input type="file" name="pdf_scan_kk"
                            class="form-control text-kecil @error('pdf_scan_kk') is-invalid @enderror"
                            accept="application/pdf">
                        @error('pdf_scan_kk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted-small">Hanya file PDF, maksimal 10MB.</small>
                    </div>
                </div>

                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Simpan</button>
                    <a href="{{ route('petani.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Script Choices.js --}}
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            if (statusSelect) {
                new Choices(statusSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Pilih Status',
                    allowHTML: true
                });
            }
        });
    </script>
@endsection
