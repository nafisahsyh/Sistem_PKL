@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        {{-- Header dengan tombol kembali --}}
        <div class="d-flex align-items-center mb-4 gap-2">

            {{-- Tombol kembali --}}
            <a href="{{ route('bagi-hasil-bulanan.index', [
                'page' => request('page'),
                'search' => request('search'),
                'id_desa' => request('id_desa'),
                'id_tahun_tanam' => request('id_tahun_tanam'),
                'bulan_start' => request('bulan_start'),
                'bulan_end' => request('bulan_end'),
            ]) }}"
                class="btn btn-success p-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            {{-- Judul --}}
            <h4 class="text-brown mb-0">Detail Bagi Hasil Bulanan</h4>

            {{-- Tombol Aksi (PDF) di kanan --}}
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('bagi-hasil-bulanan.pdf', $bulanan->id_bagi_bulanan) }}" class="btn btn-danger shadow-sm"
                    target="_blank">
                    <i class="fa fa-file-pdf"></i> Cetak PDF
                </a>
            </div>
        </div>

        {{-- Informasi Bulanan --}}
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Informasi Area</h5>
                <table class="table table-bordered mb-0 text-center table-custom align-middle">
                    <thead style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Luasan Total</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Tanggal Bagi</th>
                            <th>Total Bagian (20%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $namaBulan = [
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
                        <tr style="background-color: #ffffff; color: #014C2D;">
                            <td>{{ $bulanan->desa->desa }}</td>
                            <td>{{ $bulanan->tahunTanam->tahun }}</td>
                            <td>{{ number_format($totalLuasHa, 2, ',', '.') }}</td>
                            <td>{{ $namaBulan[$bulanan->bulan] ?? '-' }}</td>
                            <td>{{ $bulanan->tahun }}</td>
                            <td>
                                {{ $bulanan->tanggal_bagi ? \Carbon\Carbon::parse($bulanan->tanggal_bagi)->format('d-m-Y') : '-' }}
                            </td>
                            <td>Rp {{ number_format($bulanan->total_bagian, 0, ',', '.') }}</td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rincian Petani --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-0">
                    <h5 class="mb-0">Daftar Petani</h5>

                    {{-- Form Search Petani di kanan atas --}}
                    <form action="{{ route('bagi-hasil-bulanan.show', $bulanan->id_bagi_bulanan) }}" method="GET"
                        class="d-flex align-items-center">
                        <input type="text" name="search" class="form-control me-2"
                            placeholder="Cari No Plasma, No Koperasi, atau Nama Petani..." value="{{ request('search') }}"
                            style="width: 300px; height: 38px; font-size: 0.9rem;">
                        <button class="btn btn-success d-flex align-items-center justify-content-center" type="submit"
                            style="height: 38px; width: 38px;" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('bagi-hasil-bulanan.show', $bulanan->id_bagi_bulanan) }}"
                            class="btn btn-primary ms-2 d-flex align-items-center justify-content-center"
                            style="height: 38px; width: 38px;" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>No Koperasi</th>
                            <th>Nama Petani</th>
                            <th>Luas Lahan (Ha)</th>
                            <th>Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($petaniData as $p)
                            <tr>
                                <td class="text-center">
                                    {{ $noStart + $loop->index }}
                                </td>
                                <td class="text-center">{{ $p['no_plasma'] ?? '-' }}</td>
                                <td class="text-center">{{ $p['no_koperasi'] ?? '-' }}</td>
                                <td>{{ $p['nama_petani'] }}</td>
                                <td class="text-center">{{ number_format($p['luas_ha'], 2, ',', '.') ?? '-' }}</td>
                                <td class="text-right">Rp {{ number_format($p['nominal'], 0, ',', '.') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                                    <div>Belum ada data petani</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    {{ $petaniData->links('vendor.pagination.grouped') }}
                </div>
            </div>
        </div>
    @endsection
