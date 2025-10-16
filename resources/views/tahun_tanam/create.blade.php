@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Tambah Tahun</h3>

        <div class="card p-4">
            <form action="{{ route('tahun_tanam.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="text" class="form-control @error('tahun') is-invalid @enderror" id="tahun"
                        name="tahun" value="{{ old('tahun') }}" required>
                    @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('tahun_tanam.index') }}" class="btn btn-danger">Batal</a>
            </form>
        </div>
    </div>
@endsection
