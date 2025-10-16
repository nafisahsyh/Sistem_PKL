@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h2 class="mt-4 text-brown">Edit Desa</h2>

        <div class="card p-4 shadow-sm rounded-3">
            <form action="{{ route('desa.update', $desa->id_desa) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Pilihan Kecamatan --}}
                <div class="mb-3">
                    <label for="id_kecamatan" class="form-label">Kecamatan</label>
                    <select name="id_kecamatan" id="id_kecamatan"
                        class="form-select text-kecil @error('id_kecamatan') is-invalid @enderror" required>
                        <option value="" disabled selected hidden>Pilih Kecamatan</option>
                        @foreach ($kecamatan as $item)
                            <option value="{{ $item->id_kecamatan }}"
                                {{ old('id_kecamatan', $desa->id_kecamatan) == $item->id_kecamatan ? 'selected' : '' }}>
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
                    <input type="text" class="form-control text-kecil @error('desa') is-invalid @enderror" id="desa"
                        name="desa" value="{{ old('desa', $desa->desa) }}" placeholder="Perbarui nama desa" required>
                    @error('desa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Perbarui</button>
                <a href="{{ route('desa.index') }}" class="btn btn-danger">Batal</a>
            </form>
        </div>
    </div>
@endsection
