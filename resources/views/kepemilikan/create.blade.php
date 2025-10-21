@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
<div class="container-fluid px-4 mt-5">
    <h4 class="mt-4 text-brown">Tambah Kepemilikan</h4>

    <div class="card p-4">
        <form action="{{ route('kepemilikan.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="petani_id" class="form-label">Nama Petani</label>
                    <select name="petani_id" id="petani_id"
                        class="form-select text-kecil @error('petani_id') is-invalid @enderror" required>
                        <option value="" hidden>Pilih Petani</option>
                        @foreach($petani as $p)
                            <option value="{{ $p->id }}" {{ old('petani_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('petani_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="nomor_shm" class="form-label">Nomor SHM</label>
                    <input type="text" name="nomor_shm"
                        class="form-control text-kecil @error('nomor_shm') is-invalid @enderror"
                        value="{{ old('nomor_shm') }}" placeholder="Masukkan nomor SHM" required>
                    @error('nomor_shm')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="luas_lahan" class="form-label">Luas Lahan (m²)</label>
                    <input type="number" name="luas_lahan"
                        class="form-control text-kecil @error('luas_lahan') is-invalid @enderror"
                        value="{{ old('luas_lahan') }}" placeholder="Masukkan luas lahan" required>
                    @error('luas_lahan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="lokasi_lahan" class="form-label">Lokasi Lahan</label>
                    <input type="text" name="lokasi_lahan"
                        class="form-control text-kecil @error('lokasi_lahan') is-invalid @enderror"
                        value="{{ old('lokasi_lahan') }}" placeholder="Masukkan lokasi lahan" required>
                    @error('lokasi_lahan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="status_kepemilikan" class="form-label">Status Kepemilikan</label>
                <select name="status_kepemilikan" id="status_kepemilikan"
                    class="form-select text-kecil @error('status_kepemilikan') is-invalid @enderror" required>
                    <option value="" hidden>Pilih status</option>
                    <option value="aktif" {{ old('status_kepemilikan') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status_kepemilikan') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status_kepemilikan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Simpan</button>
                <a href="{{ route('kepemilikan.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
