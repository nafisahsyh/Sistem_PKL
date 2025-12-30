@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Tambah Bagi Hasil Per Bulan</h4>

        <div class="card p-4">
            <form action="{{ route('bagi-hasil-bulanan.store-bulanan') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label for="id_desa" class="form-label">Desa <span class="text-danger">*</span></label>
                        <select name="id_desa" id="id_desa"
                            class="form-select text-kecil @error('id_desa') is-invalid @enderror" required>
                            @foreach ($desa as $d)
                                <option value="{{ $d->id_desa }}" {{ old('id_desa') == $d->id_desa ? 'selected' : '' }}>
                                    {{ $d->desa }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_desa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="id_tahun_tanam" class="form-label">Tahun Tanam <span
                                class="text-danger">*</span></label>
                        <select name="id_tahun_tanam" id="id_tahun_tanam"
                            class="form-select text-kecil @error('id_tahun_tanam') is-invalid @enderror" required>
                            @foreach ($tahunTanam as $t)
                                <option value="{{ $t->id_tahun_tanam }}"
                                    {{ old('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                    {{ $t->tahun }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tahun_tanam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Luas Lahan</label>
                        <input type="text" id="total_luas" class="form-control text-kecil" value="0 Ha" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>

                        <select name="bulan" id="bulan"
                            class="form-select text-kecil @error('bulan') is-invalid @enderror" required>

                            @php
                                $bulan = [
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember',
                                ];
                            @endphp

                            @foreach ($bulan as $key => $nama)
                                <option value="{{ $key }}" {{ old('bulan') == $key ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>

                        @error('bulan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun"
                            class="form-control text-kecil @error('tahun') is-invalid @enderror"
                            value="{{ old('tahun', date('Y')) }}" required>
                        @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sisa Saldo Periode Sebelumnya</label>
                        <div class="input-group">
                            <span class="input-group-text rp-addon">Rp</span>
                            <input type="text" id="sisa_saldo" class="form-control text-kecil" value="0" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_bagi" class="form-label">Tanggal Bagi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_bagi"
                            class="form-control text-kecil @error('tanggal_bagi') is-invalid @enderror"
                            value="{{ old('tanggal_bagi', date('Y-m-d')) }}" required>
                        @error('tanggal_bagi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="total_bagian" class="form-label">Total Bagian (20%) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text rp-addon">Rp</span>
                            <input type="text" name="total_bagian" id="total_bagian"
                                class="form-control text-kecil @error('total_bagian') is-invalid @enderror"
                                value="{{ old('total_bagian', 0) }}" placeholder="Masukkan total bagian" required>
                            @error('total_bagian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <input type="hidden" name="bulan_awal" id="bulan_awal">

                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Simpan</button>
                    <a href="{{ route('bagi-hasil-bulanan.index') }}" class="btn btn-danger">Batal</a>
                </div>

            </form>
        </div>
    </div>

    {{-- Script Choices.js --}}
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const hasFormError = {{ $errors->any() ? 'true' : 'false' }};
            const disableAutoFetch = hasFormError;

            const desaSelect = document.getElementById('id_desa');
            const tahunSelect = document.getElementById('id_tahun_tanam');
            const bulanSelect = document.getElementById('bulan');
            const tahunInput = document.getElementsByName('tahun')[0];
            const bulanAwal = document.getElementById('bulan_awal');
            const totalLuasInput = document.getElementById('total_luas');
            const sisaSaldoInput = document.getElementById('sisa_saldo');
            const totalBagian = document.getElementById('total_bagian');

            // === Fungsi Update Periode ===
            function updatePeriode() {
                if (bulanSelect.value && tahunInput.value) {
                    const bulan1 = parseInt(bulanSelect.value);
                    bulanAwal.value = tahunInput.value + "-" + String(bulan1).padStart(2, '0');
                }
            }

            bulanSelect.addEventListener('change', updatePeriode);
            tahunInput.addEventListener('input', updatePeriode);

            
            // === Format Rupiah ===
            function formatRupiah(value) {
                if (!value) return '';
                value = value.toString().replace(/\D/g, '');
                return new Intl.NumberFormat('id-ID').format(value);
            }

            totalBagian.value = formatRupiah(totalBagian.value);
            totalBagian.addEventListener('input', function() {
                this.value = formatRupiah(this.value);
            });

            // === Inisialisasi Choices ===
            const choicesOptionsDesa = {
                shouldSort: false,
                placeholderValue: "Pilih Desa",
                searchPlaceholderValue: "Cari desa...",
                maxItemVisible: 5
            };
            const choicesOptionsTahun = {
                shouldSort: false,
                placeholderValue: "Pilih Tahun Tanam",
                searchPlaceholderValue: "Cari tahun tanam...",
                maxItemVisible: 5
            };
            const choicesOptionsBulan = {
                shouldSort: false,
                placeholderValue: "Pilih Bulan",
                searchPlaceholderValue: "Cari bulan...",
                maxItemVisible: 5
            };

            if (desaSelect) new Choices(desaSelect, choicesOptionsDesa);
            if (tahunSelect) new Choices(tahunSelect, choicesOptionsTahun);

            // === Hanya rebuild bulan kalau TIDAK ada error ===
            if (bulanSelect && !disableAutoFetch) {
                new Choices(bulanSelect, choicesOptionsBulan);
            }

            // === Fetch Total Luas ===
            function fetchTotalLuas() {
                if (disableAutoFetch) return;

                const idDesa = desaSelect.value;
                const idTahun = tahunSelect.value;

                if (idDesa && idTahun) {
                    fetch(`{{ route('bagi-hasil.total-luas') }}?id_desa=${idDesa}&id_tahun_tanam=${idTahun}`)
                        .then(res => res.json())
                        .then(data => {
                            const luas = data.total_luas ?? 0;
                            const luasFormatted = luas.toString().replace('.', ',');
                            totalLuasInput.value = luasFormatted + ' Ha';
                        })
                        .catch(err => {
                            console.error('Error fetch total luas:', err);
                            totalLuasInput.value = '0 Ha';
                        });
                } else {
                    totalLuasInput.value = '0 Ha';
                }
            }

            desaSelect.addEventListener('change', fetchTotalLuas);
            tahunSelect.addEventListener('change', fetchTotalLuas);

            // === Fetch Sisa Saldo ===
            function isBulanAwalPeriode(bulan) {
                return [1, 3, 5, 7, 9, 11].includes(parseInt(bulan));
            }

            function fetchSisaSaldo() {
                if (disableAutoFetch) return;

                if (!desaSelect.value || !tahunSelect.value || !bulanAwal.value) {
                    sisaSaldoInput.value = '0';
                    return;
                }

                const bulanDipilih = bulanSelect.value;
                if (!isBulanAwalPeriode(bulanDipilih)) {
                    sisaSaldoInput.value = '0';
                    return;
                }

                fetch(
                        `{{ route('bagi-hasil.sisa-saldo') }}?id_desa=${desaSelect.value}&id_tahun_tanam=${tahunSelect.value}&bulan_awal=${bulanAwal.value}`)
                    .then(res => res.json())
                    .then(data => {
                        sisaSaldoInput.value = new Intl.NumberFormat('id-ID').format(data.sisa_saldo ?? 0);
                    });
            }

            desaSelect.addEventListener('change', () => {
                fetchTotalLuas();
                fetchSisaSaldo();
            });
            tahunSelect.addEventListener('change', () => {
                fetchTotalLuas();
                fetchSisaSaldo();
            });
            bulanSelect.addEventListener('change', () => {
                updatePeriode();
                fetchSisaSaldo();
            });
            tahunInput.addEventListener('input', () => {
                updatePeriode();
                fetchSisaSaldo();
            });

            updatePeriode();

        });
    </script>
@endsection
