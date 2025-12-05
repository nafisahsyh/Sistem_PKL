@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

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

        $filters = [
            'desa' => request('id_desa'),
            'tahun' => request('id_tahun_tanam'),
            'tipe' => request('tipe'),
        ];

        $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();
    @endphp

    <div class="container-fluid px-4 mt-5">

        {{-- HEADER + FILTER BUTTON --}}
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="text-brown mb-0">Buku Besar</h3>
        </div>

        {{-- TABLE CARD --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="mb-3">
                    <button
                        class="btn d-flex align-items-center gap-2
                    {{ $jumlahFilterAktif > 0 ? 'btn-success text-white' : 'btn-outline-success' }}"
                        data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter"></i> Filter
                        @if ($jumlahFilterAktif > 0)
                            <span class="badge bg-warning text-dark">{{ $jumlahFilterAktif }}</span>
                        @endif
                    </button>
                </div>

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>Nama Petani</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Luasan (Ha)</th>
                            <th>Periode</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataTransaksi as $trx)
                            @php
                                $noPlasma = $trx->nomor_plasma ?? '-';
                                $namaPetani = $trx->nama_petani ?? '-';
                                $desaNama = $trx->nama_desa ?? '-';
                                $tahun = $trx->tahun_tanam ?? '-';
                                $luasan = $trx->luasan ?? '-';
                                $periode = $trx->periode_string ?? '-';
                                $totalNominal = $trx->total_nominal ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($dataTransaksi->currentPage() - 1) * $dataTransaksi->perPage() }}
                                </td>
                                <td class="text-center">{{ $noPlasma }}</td>
                                <td>{{ $namaPetani }}</td>
                                <td class="text-center">{{ $desaNama }}</td>
                                <td class="text-center">{{ $tahun }}</td>
                                <td class="text-center">{{ $luasan }}</td>
                                <td>{{ $periode }}</td>
                                <td class="text-end">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('buku-besar.detail', [$trx->id_petani, $trx->bulan_awal, $trx->bulan_akhir]) }}"
                                        class="btn btn-sm btn-info {{ $trx->id_petani ? '' : 'disabled' }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                    Belum ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $dataTransaksi->links('vendor.pagination.grouped') }}
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL FILTER --}}
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-filter me-2"></i> Filter Data Kepemilikan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('buku-besar.index') }}" method="GET">
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Desa</label>
                            <select name="id_desa" class="form-select">
                                <option value="">Semua Desa</option>
                                @if ($desa && $desa->count() > 0)
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}"
                                            {{ request('id_desa') == $d->id_desa ? 'selected' : '' }}>
                                            {{ $d->desa }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Tahun Tanam</label>
                            <select name="id_tahun_tanam" class="form-select">
                                <option value="">Semua Tahun Tanam</option>
                                @if ($tahunTanam && $tahunTanam->count() > 0)
                                    @foreach ($tahunTanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}"
                                            {{ request('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                            {{ $t->tahun }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jenis Transaksi</label>
                            <select name="tipe" class="form-select">
                                <option value="credit_bagihasil"
                                    {{ request('tipe') == 'credit_bagihasil' ? 'selected' : '' }}>Kredit</option>
                                <option value="debit_pengambilan"
                                    {{ request('tipe') == 'debit_pengambilan' ? 'selected' : '' }}>Debit</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('buku-besar.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
