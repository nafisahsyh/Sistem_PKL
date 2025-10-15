@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
<div class="container-fluid px-4 mt-5">
    <h2 class="mt-4 text-brown">Edit Kecamatan</h2>

    <div class="card p-4">
        <form action="{{ route('kecamatan.update', $kecamatan->id_kecamatan) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="kecamatan" class="form-label">Nama Kecamatan</label>
                <input type="text" class="form-control" id="kecamatan" name="kecamatan"
                    value="{{ $kecamatan->kecamatan }}" required>
            </div>
            <button type="submit" class="btn btn-success">Perbarui</button>
            <a href="{{ route('kecamatan.index') }}" class="btn btn-danger">Batal</a>
        </form>
    </div>
</div>
@endsection
