@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h2 class="mt-4 text-brown">Edit Tahun</h2>

        <div class="card p-4 shadow-sm rounded-3">
            <form action="{{ route('tahun_tanam.update', $tahun_tanam->id_tahun_tanam) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="text" class="form-control @error('tahun') is-invalid @enderror" id="tahun"
                        name="tahun" value="{{ old('tahun', $tahun_tanam->tahun) }}" required>
                    @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Perbarui</button>
                <a href="{{ route('tahun_tanam.index') }}" class="btn btn-danger">Batal</a>
            </form>
        </div>
    </div>
@endsection
