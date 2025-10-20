@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@section('content')
<div class="container-fluid px-4 mt-5">
    <h4 class="mt-4 text-brown">Edit Desa</h4>

    <div class="card p-4 shadow-sm rounded-3">
        <form action="{{ route('desa.update', $desa->id_desa) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Pilihan Kecamatan --}}
                <div class="col-md-6 mb-3">
                    <label for="id_kecamatan" class="form-label">Kecamatan</label>
                    <select name="id_kecamatan" id="id_kecamatan"
                        class="form-select text-kecil @error('id_kecamatan') is-invalid @enderror" required>
                        <option value="" disabled hidden>Pilih Kecamatan</option>
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
                <div class="col-md-6 mb-3">
                    <label for="desa" class="form-label">Nama Desa</label>
                    <input type="text" class="form-control text-kecil @error('desa') is-invalid @enderror"
                        id="desa" name="desa" value="{{ old('desa', $desa->desa) }}" placeholder="Perbarui nama desa" required>
                    @error('desa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="text-start mt-3">
                <button type="submit" class="btn btn-success me-2">Perbarui</button>
                <a href="{{ route('desa.index') }}" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kecamatanSelect = document.getElementById('id_kecamatan');
    new Choices(kecamatanSelect, {
        searchEnabled: false,
        itemSelectText: '',
        shouldSort: false,
        placeholder: true,
        placeholderValue: 'Pilih Kecamatan',
        allowHTML: true
    });
});
</script>
@endsection
