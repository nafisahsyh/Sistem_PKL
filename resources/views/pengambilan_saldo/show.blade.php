@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            <a href="{{ route('pengambilan.index') }}" class="btn btn-success p-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>
            <h4 class="text-brown mb-0">Detail Pengambilan Saldo</h4>
        </div>

        {{-- Informasi Desa & Periode --}}
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Informasi Periode</h5>
                @php
                    $bulanIndonesia = [
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
                <table class="table table-bordered table-custom align-middle mb-0 text-center">
                    <thead style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Periode</th>
                            <th>Total Bagi Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #ffffff; color: #014C2D;">
                            <td>{{ $desa->desa }}</td>
                            <td>{{ $tahunTanam->tahun }}</td>
                            <td>
                                {{ $bulanIndonesia[$bulan_awal] ?? $bulan_awal }}
                                @if ($bulan_awal != $bulan_akhir)
                                    - {{ $bulanIndonesia[$bulan_akhir] ?? $bulan_akhir }}
                                @endif
                                {{ $tahun }}
                            </td>
                            <td>Rp {{ number_format($total_periode, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daftar Petani --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Petani</h5>
                    {{-- Search --}}
                    {{-- Search Form --}}
                    <form action="{{ route('pengambilan.show') }}" method="GET" class="d-flex align-items-center">
                        <input type="hidden" name="id_bulanan[]" value="{{ implode(',', (array) request('id_bulanan')) }}">
                        <input type="text" name="search" class="form-control me-2"
                            placeholder="Cari Nomor atau Nama Petani..." value="{{ request('search') }}"
                            style="width: 300px; height: 38px; font-size: 0.9rem;">
                        <button class="btn btn-success d-flex align-items-center justify-content-center" type="submit"
                            style="height: 38px; width: 38px;" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('pengambilan.show', ['id_bulanan' => request('id_bulanan')]) }}"
                            class="btn btn-primary ms-2 d-flex align-items-center justify-content-center"
                            style="height: 38px; width: 38px;" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>

                </div>

                <table class="table table-bordered table-striped table-custom align-middle">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>No Koperasi</th>
                            <th>Nama Petani</th>
                            <th>Luas Lahan (Ha)</th>
                            <th>Total Nominal (Rp)</th>
                            <th>Aksi Ambil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($petaniData as $i => $p)
                            <tr>
                                <td class="text-center">{{ $noStart + $i }}</td>
                                <td class="text-center">{{ $p['no_plasma'] ?? '-' }}</td>
                                <td class="text-center">{{ $p['no_koperasi'] ?? '-' }}</td>
                                <td>{{ $p['nama_petani'] }}</td>
                                <td class="text-center">{{ number_format($p['luas_ha'], 2) }}</td>
                                <td class="text-end">Rp {{ number_format($p['nominal'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#modalAmbil_{{ $p['id_petani'] }}">
                                        <i class="fa fa-file-invoice-dollar"></i>
                                    </button>

                                    {{-- Modal Ambil --}}
                                    <div class="modal fade" id="modalAmbil_{{ $p['id_petani'] }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">Ambil Saldo</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Ambil saldo untuk <strong>{{ $p['nama_petani'] }}</strong>?</p>
                                                    <p>Total: <strong>Rp
                                                            {{ number_format($p['nominal'], 0, ',', '.') }}</strong></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button class="btn btn-primary" disabled>Ambil (belum dibuat)</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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
    </div>
@endsection
