@extends('theme.default')

@section('content')
    @php
        $filterAktif = request()->filled('id_desa') || request()->filled('id_tahun_tanam');
    @endphp

    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">

        {{-- HEADER + BUTTON TAMBAH --}}
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="text-brown mb-0">Bagi Hasil Per Bulan</h3>
            <a href="{{ route('bagi-hasil-bulanan.list-pdf') }}?id_desa={{ request('id_desa') }}&id_tahun_tanam={{ request('id_tahun_tanam') }}"
                target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
        </div>

        {{-- CARD TABLE --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">

                {{-- FILTER BUTTON + SEARCH --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div class="d-flex gap-2">
                        {{-- TOMBOL TAMBAH --}}
                        <a href="{{ route('bagi-hasil-bulanan.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                        </a>

                        {{-- BUTTON FILTER --}}
                        <button class="btn {{ $filterAktif ? 'btn-success text-white' : 'btn-outline-success' }}"
                            data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    {{-- SEARCH pindahkan ke sini --}}
                    <form action="{{ route('bagi-hasil-bulanan.index') }}" method="GET"
                        class="d-flex align-items-start flex-wrap ms-auto">

                        {{-- Keep filters --}}
                        @if (request()->filled('id_desa'))
                            <input type="hidden" name="id_desa" value="{{ request('id_desa') }}">
                        @endif
                        @if (request()->filled('id_tahun_tanam'))
                            <input type="hidden" name="id_tahun_tanam" value="{{ request('id_tahun_tanam') }}">
                        @endif

                        <input type="text" name="search" class="form-control form-control-search me-2"
                            placeholder="Cari desa atau tahun tanam..." value="{{ request('search') }}"
                            style="width: 250px;">

                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-search"></i>
                        </button>

                        <a href="{{ route('bagi-hasil-bulanan.index') }}" class="btn btn-primary ms-2" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                {{-- TABLE --}}
                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Luasan Total</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Tanggal Bagi</th>
                            <th>Total Bagian (20%)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bulanan as $b)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($bulanan->currentPage() - 1) * $bulanan->perPage() }}
                                </td>
                                <td>{{ $b->desa->desa }}</td>
                                <td class="text-center">{{ $b->tahunTanam->tahun }}</td>
                                <td class="text-center">
                                    @php
                                        $totalLuasM2 = \App\Models\DetailKepemilikan::where('status_pengelolaan', 'ksm')
                                            ->where('status_kepemilikan', 'aktif')
                                            ->whereHas(
                                                'lahan',
                                                fn($q) => $q
                                                    ->where('id_desa', $b->id_desa)
                                                    ->where('id_tahun_tanam', $b->id_tahun_tanam),
                                            )
                                            ->with('lahan')
                                            ->get()
                                            ->sum(fn($item) => $item->lahan->luas_peta ?? 0);

                                        $totalLuasHa = $totalLuasM2 / 10000;
                                    @endphp

                                    {{ number_format($totalLuasHa, 2) }} Ha
                                </td>
                                <td class="text-center">
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
                                    {{ $bulanIndonesia[$b->bulan] ?? '-' }}
                                </td>

                                <td class="text-center">{{ $b->tahun }}</td>

                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($b->tanggal_bagi)->format('d-m-Y') }}
                                </td>

                                <td class="text-center">
                                    Rp {{ number_format($b->total_bagian, 0, ',', '.') }}
                                </td>

                                <td class="text-center">

                                    {{-- DETAIL --}}
                                    <a href="{{ route('bagi-hasil-bulanan.show', $b->id_bagi_bulanan) }}"
                                        class="btn btn-sm btn-info me-1" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- EDIT --}}
                                    <a href="{{ route('bagi-hasil-bulanan.edit', $b->id_bagi_bulanan) }}"
                                        class="btn btn-sm btn-warning me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('bagi-hasil-bulanan.destroy', $b) }}" method="POST"
                                        class="d-inline delete-form"> @csrf @method('DELETE') <button type="button"
                                            class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
                                    </form>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                    Belum ada data bulanan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $bulanan->links('vendor.pagination.grouped') }}
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
                        <i class="fas fa-filter me-2"></i> Filter Bagi Hasil Per Bulan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('bagi-hasil-bulanan.index') }}" method="GET">
                    <div class="modal-body px-4">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Desa</label>
                            <select id="filter_desa" name="id_desa" class="form-select">
                                <option value="">Semua Desa</option>
                                @foreach ($desa as $d)
                                    <option value="{{ $d->id_desa }}"
                                        {{ request('id_desa') == $d->id_desa ? 'selected' : '' }}>
                                        {{ $d->desa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tahun Tanam</label>
                            <select id="filter_tahun_tanam" name="id_tahun_tanam" class="form-select">
                                <option value="">Semua Tahun Tanam</option>
                                @foreach ($tahunTanam as $t)
                                    <option value="{{ $t->id_tahun_tanam }}"
                                        {{ request('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                        {{ $t->tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('bagi-hasil-bulanan.index') }}" class="btn btn-secondary">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const desaSelect = document.getElementById('filter_desa');
            const tahunSelect = document.getElementById('filter_tahun_tanam');

            if (desaSelect) new Choices(desaSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari desa..."
            });
            if (tahunSelect) new Choices(tahunSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari tahun tanam..."
            });

            const btnReset = document.getElementById('resetFilter');
            if (btnReset) {
                btnReset.addEventListener('click', function() {
                    window.location.href = "{{ route('bagi-hasil-bulanan.index') }}";
                });
            }
        });
    </script>

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.delete-form');

                    Swal.fire({
                        title: "<h3 style='font-size:15px;margin-bottom:2px;line-height:0.5;color:#000;'>Yakin ingin menghapus?</h3>",
                        html: "<p style='font-size:14px;margin:0;color:#000;'>Data yang dihapus tidak dapat dikembalikan!</p>",
                        icon: 'warning',
                        iconColor: '#dc3545',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#dc3545',
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    {{-- Notifikasi sukses --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: "<h3 style='font-size:15px;margin-bottom:0;color:#000;'>Berhasil</h3>",
                text: "{{ session('success') }}",
                confirmButtonColor: '#198754',
                timer: 1800,
                showConfirmButton: false
            });
        </script>
    @endif
@endsection
