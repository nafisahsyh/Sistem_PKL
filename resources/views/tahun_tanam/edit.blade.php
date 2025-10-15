@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
<div class="container-fluid px-4 mt-5">
    <h2 class="mt-4 text-brown">Edit Tahun</h2>

    <div class="card p-4">
        <form action="{{ route('tahun_tanam.update', $tahun_tanam->id_tahun_tanam) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="tahun_tanam" class="form-label">Tahun</label>
                <input type="text" class="form-control" id="tahun" name="tahun"
                    value="{{ $tahun_tanam->tahun}}" required>
            </div>
            <button type="submit" class="btn btn-success">Perbarui</button>
            <a href="{{ route('tahun_tanam.index') }}" class="btn btn-danger">Batal</a>
        </form>
    </div>
</div>
@endsection
