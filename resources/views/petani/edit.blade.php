@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Data Petani</h4>

        <div class="card p-4 shadow-sm rounded-3">
            <form action="{{ route('petani.update', $petani->id_petani) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="page" value="{{ $page }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nomor_anggota_plasma" class="form-label">Nomor Plasma</label>
                        <input type="text"
                            class="form-control text-kecil @error('nomor_anggota_plasma') is-invalid @enderror"
                            id="nomor_anggota_plasma" name="nomor_anggota_plasma"
                            value="{{ old('nomor_anggota_plasma', $petani->nomor_anggota_plasma) }}"
                            placeholder="Masukkan nomor plasma">
                        @error('nomor_anggota_plasma')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_anggota_koperasi" class="form-label">Nomor Koperasi</label>
                        <input type="text"
                            class="form-control text-kecil @error('nomor_anggota_koperasi') is-invalid @enderror"
                            id="nomor_anggota_koperasi" name="nomor_anggota_koperasi"
                            value="{{ old('nomor_anggota_koperasi', $petani->nomor_anggota_koperasi) }}"
                            placeholder="Masukkan nomor koperasi">
                        @error('nomor_anggota_koperasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="NIK" class="form-label">NIK</label>
                        <input type="text" class="form-control text-kecil @error('NIK') is-invalid @enderror"
                            id="NIK" name="NIK" value="{{ old('NIK', $petani->NIK) }}" maxlength="16"
                            placeholder="Masukkan NIK">
                        @error('NIK')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control text-kecil @error('nama') is-invalid @enderror"
                            id="nama" name="nama" value="{{ old('nama', $petani->nama) }}"
                            placeholder="Masukan nama lengkap" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="3"
                        class="form-control text-kecil @error('alamat') is-invalid @enderror">{{ old('alamat', $petani->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="no_telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control text-kecil @error('no_telepon') is-invalid @enderror"
                            id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $petani->no_telepon) }}"
                            placeholder="0812xxxx atau +62812xxxx">
                        @error('no_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status"
                            class="form-select text-kecil @error('status') is-invalid @enderror" required>
                            <option value="" disabled hidden>Pilih Status</option>
                            <option value="aktif" {{ old('status', $petani->status) == 'aktif' ? 'selected' : '' }}>Aktif
                            </option>
                            <option value="tidak_aktif"
                                {{ old('status', $petani->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif
                            </option>
                            <option value="berhenti"
                                {{ old('status', $petani->status) == 'berhenti' ? 'selected' : '' }}>Berhenti
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
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
                    <div class="col-md-6 mb-3">
                        <label for="pdf_scan_kk" class="form-label">File Scan KK (PDF)</label>
                        <input type="file" name="pdf_scan_kk" id="pdf_scan_kk"
                            class="form-control text-kecil @error('pdf_scan_kk') is-invalid @enderror"
                            accept="application/pdf">

                        @if ($petani->pdf_scan_kk)
                            <small class="text-muted">
                                File saat ini:
                                <a href="{{ asset('storage/' . $petani->pdf_scan_kk) }}" target="_blank">Lihat PDF</a>
                            </small>
                        @endif

                        @error('pdf_scan_kk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Perbarui</button>
                    <a href="{{ route('petani.index', ['page' => $page ?? 1]) }}" class="btn btn-danger">Batal</a>
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
