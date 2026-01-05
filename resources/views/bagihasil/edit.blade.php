@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Bagi Hasil Per Bulan</h4>

        <div class="card p-4">
            <form action="{{ route('bagi-hasil-bulanan.update', $bulanan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label for="id_desa" class="form-label">Desa <span class="text-danger">*</span></label>
                        <select name="id_desa" id="id_desa"
                            class="form-select text-kecil @error('id_desa') is-invalid @enderror" disabled>
                            <option value="">Pilih Desa</option>
                            @foreach ($desa as $d)
                                <option value="{{ $d->id_desa }}"
                                    {{ old('id_desa', $bulanan->id_desa) == $d->id_desa ? 'selected' : '' }}>
                                    {{ $d->desa }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_desa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="id_desa" value="{{ $bulanan->id_desa }}">

                    <div class="col-md-4 mb-3">
                        <label for="id_tahun_tanam" class="form-label">Tahun Tanam <span
                                class="text-danger">*</span></label>
                        <select name="id_tahun_tanam" id="id_tahun_tanam"
                            class="form-select text-kecil @error('id_tahun_tanam') is-invalid @enderror" disabled>
                            <option value="">Pilih Tahun Tanam</option>
                            @foreach ($tahunTanam as $t)
                                <option value="{{ $t->id_tahun_tanam }}"
                                    {{ old('id_tahun_tanam', $bulanan->id_tahun_tanam) == $t->id_tahun_tanam ? 'selected' : '' }}>
                                    {{ $t->tahun }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tahun_tanam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="id_tahun_tanam" value="{{ $bulanan->id_tahun_tanam }}">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Luas Lahan</label>
                        <input type="text" id="total_luas" class="form-control text-kecil"
                            value="{{ $total_luas }} Ha" readonly>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                        <select name="bulan" id="bulan"
                            class="form-select text-kecil @error('bulan') is-invalid @enderror" disabled>
                            @php
                                $namaBulan = [
                                    'Januari',
                                    'Februari',
                                    'Maret',
                                    'April',
                                    'Mei',
                                    'Juni',
                                    'Juli',
                                    'Agustus',
                                    'September',
                                    'Oktober',
                                    'November',
                                    'Desember',
                                ];
                            @endphp
                            @foreach ($namaBulan as $index => $bulan)
                                <option value="{{ $index + 1 }}"
                                    {{ old('bulan', $bulanan->bulan) == $index + 1 ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                        @error('bulan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="bulan" value="{{ $bulanan->bulan }}">

                    <div class="col-md-4 mb-3">
                        <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun"
                            class="form-control text-kecil @error('tahun') is-invalid @enderror"
                            value="{{ old('tahun', $bulanan->tahun) }}" readonly>
                        @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="tahun" value="{{ $bulanan->tahun }}">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sisa Saldo Periode Sebelumnya</label>
                        <div class="input-group">
                            <span class="input-group-text rp-addon">Rp</span>
                            <input type="text" class="form-control text-kecil"
                                value="{{ number_format($bulanan->sisa_saldo_snapshot ?? 0, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Bagi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_bagi"
                            class="form-control text-kecil @error('tanggal_bagi') is-invalid @enderror"
                            value="{{ old('tanggal_bagi', optional($bulanan->tanggal_bagi)->format('Y-m-d')) }}" required>
                        @error('tanggal_bagi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="total_bagian" class="form-label">Total Bagian (20%) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text rp-addon">Rp</span>

                            @php
                                $rawTotal = old('total_bagian', (int) $bulanan->total_bagian);
                            @endphp

                            <input type="text" name="total_bagian" id="total_bagian"
                                class="form-control text-kecil @error('total_bagian') is-invalid @enderror"
                                value="{{ $rawTotal }}" placeholder="Masukkan total bagian" required>

                            @error('total_bagian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Perbarui</button>
                    <a href="{{ route('bagi-hasil-bulanan.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        const hasFormError = {{ $errors->any() ? 'true' : 'false' }};
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const desaSelect = document.getElementById('id_desa');
            const tahunSelect = document.getElementById('id_tahun_tanam');
            const bulanSelect = document.getElementById('bulan');
            const totalLuasInput = document.getElementById('total_luas');

            // Choices.js
            const choicesOptions = (placeholder, searchPlaceholder) => ({
                shouldSort: false,
                placeholderValue: placeholder,
                searchPlaceholderValue: searchPlaceholder,
                maxItemVisible: 5
            });

            const totalBagian = document.getElementById('total_bagian');

            // Format awal dari server → 6000000 jadi 6.000.000
            if (totalBagian.value) {
                let clean = totalBagian.value.replace(/\D/g, '');
                totalBagian.value = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
            }

            totalBagian.addEventListener('input', function() {
                let clean = this.value.replace(/\./g, '').replace(/\D/g, '');
                this.value = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
            });


            new Choices(desaSelect, choicesOptions("Pilih Desa", "Cari desa..."));
            new Choices(tahunSelect, choicesOptions("Pilih Tahun Tanam", "Cari tahun tanam..."));
            if (!hasFormError) {
                new Choices(bulanSelect, choicesOptions("Pilih Bulan", "Cari bulan..."));
            }

            function fetchTotalLuas() {
                const idDesa = desaSelect.value;
                const idTahun = tahunSelect.value;
                if (idDesa && idTahun) {
                    fetch(`{{ route('bagi-hasil.total-luas') }}?id_desa=${idDesa}&id_tahun_tanam=${idTahun}`)
                        .then(res => res.json())
                        .then(data => totalLuasInput.value = (data.total_luas ?? 0) + ' Ha')
                        .catch(() => totalLuasInput.value = '0 Ha');
                } else {
                    totalLuasInput.value = '0 Ha';
                }
            }

            desaSelect.addEventListener('change', fetchTotalLuas);
            tahunSelect.addEventListener('change', fetchTotalLuas);
        });
    </script>
@endsection
