@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Kecamatan</h4>

        <div class="card p-4">
            <form action="{{ route('kecamatan.update', $kecamatan->id_kecamatan) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="kecamatan" class="form-label">Nama Kecamatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-kecil @error('kecamatan') is-invalid @enderror" id="kecamatan"
                        name="kecamatan" value="{{ old('kecamatan', $kecamatan->kecamatan) }}" placeholder="Perbarui nama kecamatan" required>
                    {{-- Pesan error di bawah input --}}
                    @error('kecamatan')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('kecamatan.index') }}" class="btn btn-danger">Batal</a>
            </div>
            </form>
        </div>
    </div>
@endsection
