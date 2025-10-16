@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Tambah Desa</h3>

        <div class="card p-4">
            <form action="{{ route('desa.store') }}" method="POST">
                @csrf

                {{-- Pilihan Kecamatan --}}
                <div class="mb-3">
                    <label for="id_kecamatan" class="form-label">Kecamatan</label>
                    <select name="id_kecamatan" id="id_kecamatan"
                        class="form-select @error('id_kecamatan') is-invalid @enderror" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach ($kecamatan as $item)
                            <option value="{{ $item->id_kecamatan }}"
                                {{ old('id_kecamatan') == $item->id_kecamatan ? 'selected' : '' }}>
                                {{ $item->kecamatan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_kecamatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nama Desa --}}
                <div class="mb-3">
                    <label for="desa" class="form-label">Nama Desa</label>
                    <input type="text" class="form-control @error('desa') is-invalid @enderror" id="desa"
                        name="desa" value="{{ old('desa') }}" required>
                    @error('desa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('desa.index') }}" class="btn btn-danger">Batal</a>
            </form>
        </div>
    </div>
@endsection
