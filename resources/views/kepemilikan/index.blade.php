@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="text-brown mb-0">Data Kepemilikan</h3>
            <a href="{{ route('kepemilikan.cetakSemuaPDF', [
                'search' => request('search'),
                'desa' => request('desa'),
                'tahun' => request('tahun'),
            ]) }}"
                target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
        </div>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">

                {{-- 🔍 Filter & Search --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    {{-- Tombol Filter di kiri --}}
                    <button
                        class="btn {{ request()->filled('desa') || request()->filled('tahun') ? 'btn-success text-white' : 'btn-outline-success' }}"
                        data-bs-toggle="modal" data-bs-target="#filterModal" title="Filter Data">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    {{-- Search bar di kanan --}}
                    <form action="{{ route('kepemilikan.index') }}" method="GET"
                        class="d-flex align-items-start flex-wrap justify-content-end">

                        {{-- Biar filter tetap terkirim waktu pencarian --}}
                        @if (request()->filled('desa'))
                            <input type="hidden" name="desa" value="{{ request('desa') }}">
                        @endif
                        @if (request()->filled('tahun'))
                            <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                        @endif

                        <input type="text" name="search" class="form-control form-control-search me-2"
                            placeholder="Cari nama petani, nomor plasma, atau desa..." value="{{ request('search') }}"
                            style="width: 300px;">

                        <button class="btn btn-success" type="submit" title="Cari">
                            <i class="fas fa-search"></i>
                        </button>

                        <a href="{{ route('kepemilikan.index') }}" class="btn btn-primary ms-2" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                {{-- Tambahan baru --}}
                @php
                    // Pastikan variabel mode selalu ada supaya gak error
                    $mode = $mode ?? 'normal';
                    $isNormalMode = $mode === 'normal';
                    $isPerLahanMode = $mode === 'perLahan';
                @endphp


                {{-- 🧾 Tabel Data --}}
                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>No</th>
                            <th>Nomor Plasma</th>
                            <th>Nama Petani</th>
                            <th>Status Petani</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kepemilikan as $index => $k)
                            @php
                                $detailList = $k->detailKepemilikan->map(
                                    fn($d) => [
                                        'desa' => $d->lahan->desa->desa ?? '-',
                                        'tahun' => $d->lahan->tahunTanam->tahun ?? '-',
                                        'id_lahan' => $d->id_lahan,
                                        'status_kepemilikan' => $d->status_kepemilikan,
                                    ],
                                );

                                if ($isNormalMode) {
                                    $detailList = $detailList
                                        ->unique(fn($item) => $item['desa'] . $item['tahun'])
                                        ->values();
                                }

                                $rowNumber =
                                    $loop->iteration +
                                    ($kepemilikan instanceof \Illuminate\Pagination\LengthAwarePaginator
                                        ? ($kepemilikan->currentPage() - 1) * $kepemilikan->perPage()
                                        : 0);
                            @endphp

                            @foreach ($detailList as $i => $detail)
                                <tr>
                                    {{-- Mode normal --}}
                                    @if ($isNormalMode && $i == 0)
                                        <td class="text-center align-middle" rowspan="{{ $detailList->count() }}">
                                            {{ $rowNumber }}
                                        </td>
                                        <td class="align-middle" rowspan="{{ $detailList->count() }}">
                                            {{ $k->petani->nomor_anggota_plasma ?? '-' }}
                                        </td>
                                        <td class="align-middle" rowspan="{{ $detailList->count() }}">
                                            {{ $k->petani->nama ?? '-' }}
                                        </td>
                                        <td class="text-center align-middle" rowspan="{{ $detailList->count() }}">
                                            <span
                                                class="badge {{ $k->petani->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($k->petani->status) }}
                                            </span>
                                        </td>

                                        {{-- Mode per lahan --}}
                                    @elseif ($isPerLahanMode)
                                        <td class="text-center align-middle">{{ $rowNumber }}</td>
                                        <td>{{ $k->petani->nomor_anggota_plasma ?? '-' }}</td>
                                        <td>{{ $k->petani->nama ?? '-' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $detail['status_kepemilikan'] === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($detail['status_kepemilikan']) }}
                                            </span>
                                        </td>
                                    @endif

                                    <td>{{ $detail['desa'] }}</td>
                                    <td class="text-center">{{ $detail['tahun'] }}</td>

                                    {{-- Tombol Aksi --}}
                                    @if ($isNormalMode && $i == 0)
                                        <td class="text-center align-middle" rowspan="{{ $detailList->count() }}">
                                            <a href="{{ route('kepemilikan.show', $k->id_kepemilikan) }}"
                                                class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('kepemilikan.edit', $k->id_kepemilikan) }}"
                                                class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('kepemilikan.destroy', $k->id_kepemilikan) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @elseif ($isPerLahanMode)
                                        <td class="text-center align-middle">
                                            <a href="{{ route('kepemilikan.showPerLahan', ['id_kepemilikan' => $k->id_kepemilikan, 'id_lahan' => $detail['id_lahan']]) }}"
                                                class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('kepemilikan.editPerLahan', ['id_kepemilikan' => $k->id_kepemilikan, 'id_lahan' => $detail['id_lahan']]) }}"
                                                class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form
                                                action="{{ route('kepemilikan.destroyPerLahan', ['id_kepemilikan' => $k->id_kepemilikan, 'id_lahan' => $detail['id_lahan']]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Belum ada data kepemilikan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $kepemilikan->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Filter --}}
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title" id="filterModalLabel">
                        <i class="fas fa-filter me-2"></i> Filter Data Kepemilikan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('kepemilikan.index') }}" method="GET">
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Desa</label>
                            <select name="desa" id="filter_desa" class="form-select">
                                <option value="">Semua Desa</option>
                                @foreach ($daftarDesa as $desa)
                                    <option value="{{ $desa->desa }}"
                                        {{ request('desa') == $desa->desa ? 'selected' : '' }}>
                                        {{ $desa->desa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Tahun Tanam</label>
                            <select name="tahun" id="filter_tahun_tanam" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach ($daftarTahun as $tahun)
                                    <option value="{{ $tahun->tahun }}"
                                        {{ request('tahun') == $tahun->tahun ? 'selected' : '' }}>
                                        {{ $tahun->tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('kepemilikan.index') }}" class="btn btn-secondary">
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
            if (desaSelect) new Choices(desaSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari desa..."
            });
            if (tahunSelect) new Choices(tahunSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari tahun..."
            });
        });
    </script>

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.delete-form');
                    Swal.fire({
                        title: "Yakin ingin menghapus?",
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#dc3545',
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: "Berhasil",
                text: "{{ session('success') }}",
                timer: 1800,
                showConfirmButton: false
            });
        </script>
    @endif
@endsection
