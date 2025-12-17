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

                @php
                    $filters = [
                        'id_desa' => request('id_desa'),
                        'id_tahun_tanam' => request('id_tahun_tanam'),
                        'periode' => request('periode'),
                        'tahun' => request('tahun'),
                    ];

                    // Hitung filter aktif
                    $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();
                @endphp

                <button
                    class="btn mb-3 d-flex align-items-center gap-2
        {{ $jumlahFilterAktif > 0 ? 'btn-success' : 'btn-outline-success' }}"
                    data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter"></i>
                    Filter

                    @if ($jumlahFilterAktif > 0)
                        <span class="badge bg-warning text-dark">
                            {{ $jumlahFilterAktif }}
                        </span>
                    @endif
                </button>

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

                                <td>
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
                                    <a href="{{ route('pengambilan.show', [
                                        'id_bulanan' => $p['id_bulanan'],
                                        'id_desa' => request('id_desa'),
                                        'id_tahun_tanam' => request('id_tahun_tanam'),
                                        'periode' => request('periode'),
                                        'tahun' => request('tahun'),
                                    ]) }}"
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

    <!-- Modal Filter -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-wide">

            <div class="modal-content rounded-4">

                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">Filter Periode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form method="GET" action="{{ route('pengambilan.index') }}">
                    <div class="modal-body">

                        <div class="row g-3">

                            {{-- Desa --}}
                            <div class="col-md-6">
                                <label class="form-label">Desa</label>
                                <select name="id_desa" id=filter_desa class="form-select">
                                    <option value="">Semua Desa</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}"
                                            {{ request('id_desa') == $d->id_desa ? 'selected' : '' }}>
                                            {{ $d->desa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Periode 2 Bulan --}}
                            <div class="col-md-6">
                                <label class="form-label">Periode (2 Bulanan)</label>
                                <select name="periode" id="filter_periode" class="form-select">
                                    <option value="">Semua Periode</option>
                                    <option value="1" {{ request('periode') == 1 ? 'selected' : '' }}>Januari–Februari
                                    </option>
                                    <option value="2" {{ request('periode') == 2 ? 'selected' : '' }}>Maret–April
                                    </option>
                                    <option value="3" {{ request('periode') == 3 ? 'selected' : '' }}>Mei–Juni
                                    </option>
                                    <option value="4" {{ request('periode') == 4 ? 'selected' : '' }}>Juli–Agustus
                                    </option>
                                    <option value="5" {{ request('periode') == 5 ? 'selected' : '' }}>
                                        September–Oktober</option>
                                    <option value="6" {{ request('periode') == 6 ? 'selected' : '' }}>
                                        November–Desember</option>
                                </select>
                            </div>

                            {{-- Tahun Tanam --}}
                            <div class="col-md-6">
                                <label class="form-label">Tahun Tanam</label>
                                <select name="id_tahun_tanam" id="filter_tahun_tanam" class="form-select">
                                    <option value="">Semua Tahun Tanam</option>
                                    @foreach ($tahunTanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}"
                                            {{ request('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                            {{ $t->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tahun --}}
                            <div class="col-md-6">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="tahun" class="form-control" placeholder="Misal: 2025"
                                    value="{{ request('tahun') }}" min="2000" max="3000" />

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('pengambilan.index') }}" class="btn btn-secondary">
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

    {{-- Choices.js --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const desaSelect = document.getElementById('filter_desa');
            const tahunSelect = document.getElementById('filter_tahun_tanam');
            const statusSelect = document.getElementById('filter_periode');

            if (desaSelect) new Choices(desaSelect, {
                shouldSort: false,
                placeholder: true,
                placeholderValue: "Semua Desa",
                searchPlaceholderValue: "Cari desa..."
            });

            if (tahunSelect) new Choices(tahunSelect, {
                shouldSort: false,
                placeholder: true,
                placeholderValue: "Semua Tahun Tanam",
                searchPlaceholderValue: "Cari tahun..."
            });

            if (statusSelect) new Choices(statusSelect, {
                shouldSort: false,
                placeholder: true,
                placeholderValue: "Semua Periode",
                searchPlaceholderValue: "Cari periode..."

            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('resetFilter');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    // navigasi paksa ke route index tanpa query
                    window.location.href = "{{ route('pengambilan.index') }}";
                });
            }
        });
    </script>
@endsection
