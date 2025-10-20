@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Tambah Tahun Tanam</h4>

        <div class="card p-4">
            <form action="{{ route('tahun_tanam.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun Tanam</label>
                    <input type="text" class="form-control text-kecil @error('tahun') is-invalid @enderror" id="tahun"
                        name="tahun" value="{{ old('tahun') }}" placeholder="Masukkan tahun tanam" required>
                    @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Simpan</button>
                    <a href="{{ route('tahun_tanam.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
