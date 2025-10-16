@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Tambah Kecamatan</h3>

        <div class="card p-4">
            <form action="{{ route('kecamatan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="kecamatan" class="form-label">Nama Kecamatan</label>
                    <input type="text" class="form-control @error('kecamatan') is-invalid @enderror" id="kecamatan"
                        name="kecamatan" value="{{ old('kecamatan') }}" required>
                    {{-- Pesan error langsung di bawah input --}}
                    @error('kecamatan')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('kecamatan.index') }}" class="btn btn-danger">Batal</a>
            </form>
        </div>
    </div>
@endsection
