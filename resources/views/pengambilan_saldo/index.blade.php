@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="text-brown mb-0">Bagi Hasil Periode</h3>
        </div>

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

        <div class="card shadow-sm rounded-3">
            <div class="card-body p-4">

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Periode</th>
                            <th>Tanggal Bagi</th>
                            <th>Total Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periode as $i => $p)
                            <tr>
                                <td class="text-center">{{ $periode->firstItem() + $i }}</td>
                                <td>{{ $p['desa']['desa'] ?? '-' }}</td>
                                <td class="text-center">{{ $p['tahunTanam']['tahun'] ?? '-' }}</td>

                                <td class="text-center">
                                    @php
                                        $bulanAwalTampil = $p['bulan_awal'] ?? 1;
                                        $bulanAkhirTampil = $p['bulan_akhir'] ?? $bulanAwalTampil;
                                    @endphp
                                    {{ $namaBulan[$bulanAwalTampil] ?? $bulanAwalTampil }} -
                                    {{ $namaBulan[$bulanAkhirTampil] ?? $bulanAkhirTampil }} {{ $p['tahun'] ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ isset($p['tanggal_bagi']) ? \Carbon\Carbon::parse($p['tanggal_bagi'])->format('d-m-Y') : '-' }}
                                </td>

                                <td class="text-end">Rp {{ number_format($p['total_periode'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('pengambilan.show', ['id_bulanan' => $p['id_bulanan']]) }}"
                                        class="btn btn-sm btn-info me-1" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                    Belum ada data periode
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $periode->links('vendor.pagination.grouped') }}
                </div>

            </div>
        </div>
    </div>
@endsection
