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
                            <th>Kode</th>
                            <th>Status Kelola</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kepemilikan as $index => $k)
                            @php
                                // Group detail berdasarkan desa dan tahun tanam
                                $grouped = $k->detailKepemilikan->groupBy(
                                    fn($d) => ($d->lahan->desa->desa ?? '-') .
                                        '-' .
                                        ($d->lahan->tahunTanam->tahun ?? '-'),
                                );

                                $rowNumber =
                                    $loop->iteration +
                                    ($kepemilikan instanceof \Illuminate\Pagination\LengthAwarePaginator
                                        ? ($kepemilikan->currentPage() - 1) * $kepemilikan->perPage()
                                        : 0);
                            @endphp

                            @php $firstRow = true; @endphp
                            @foreach ($grouped as $group)
                                @foreach ($group as $i => $detail)
                                    <tr>
                                        {{-- tampilkan kolom petani hanya di baris pertama --}}
                                        @if ($firstRow)
                                            <td class="text-center align-middle"
                                                rowspan="{{ $k->detailKepemilikan->count() }}">
                                                {{ $rowNumber }}
                                            </td>
                                            <td class="align-middle" rowspan="{{ $k->detailKepemilikan->count() }}">
                                                {{ $k->petani->nomor_anggota_plasma ?? '-' }}
                                            </td>
                                            <td class="align-middle" rowspan="{{ $k->detailKepemilikan->count() }}">
                                                {{ $k->petani->nama ?? '-' }}
                                            </td>
                                            <td class="text-center align-middle"
                                                rowspan="{{ $k->detailKepemilikan->count() }}">
                                                @if ($k->petani->status === 'aktif')
                                                    <span class="badge bg-success">Aktif</span>
                                                @elseif($k->petani->status === 'berhenti')
                                                    <span class="badge bg-danger">Berhenti</span>
                                                @elseif($k->petani->status === 'tidak_aktif')
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary">{{ ucfirst($k->petani->status) }}</span>
                                                @endif
                                            </td>
                                        @endif

                                        {{-- kolom desa & tahun (gabung kalau sama) --}}
                                        @if ($i === 0)
                                            <td class="align-middle text-center" rowspan="{{ count($group) }}">
                                                {{ $detail->lahan->desa->desa ?? '-' }}
                                            </td>
                                            <td class="align-middle text-center" rowspan="{{ count($group) }}">
                                                {{ $detail->lahan->tahunTanam->tahun ?? '-' }}
                                            </td>
                                        @endif

                                        {{-- kolom kode lahan, tampil tiap baris --}}
                                        <td class="text-center">{{ $detail->kode_lahan ?? '-' }}</td>
                                        <td class="text-center">
                                            {{ $detail->status_pengelolaan ?? '-' }}
                                        </td>

                                        {{-- tombol aksi tampil sekali di baris pertama petani --}}
                                        @if ($firstRow)
                                            @php
                                                // Hitung jumlah lahan aktif di kepemilikan ini
                                                $jumlahLahan = $k->detailKepemilikan->count();

                                                // Tentukan apakah harus tampil per lahan
                                                // Sekarang termasuk juga kalau ada "search"
                                                $tampilPerLahan =
                                                    request()->filled('desa') ||
                                                    request()->filled('tahun') ||
                                                    request()->filled('status_pengelolaan') ||
                                                    request()->filled('search');
                                            @endphp

                                            <td class="text-center align-middle" rowspan="{{ $jumlahLahan }}">
                                                @if ($tampilPerLahan && isset($detail) && $detail->lahan)
                                                    {{-- Mode per lahan --}}
                                                    <a href="{{ route('kepemilikan.showPerLahan', [
                                                        'id_kepemilikan' => $k->id_kepemilikan,
                                                        'id_lahan' => $detail->lahan->id_lahan,
                                                        'page' => request('page'),
                                                        'search' => $search,
                                                        'desa' => $desa,
                                                        'tahun' => $tahun,
                                                        'status_pengelolaan' => $status_pengelolaan,
                                                    ]) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <a href="{{ route('kepemilikan.editPerLahan', [
                                                        'id_kepemilikan' => $k->id_kepemilikan,
                                                        'id_lahan' => $detail->lahan->id_lahan,
                                                        'page' => request('page'),
                                                        'search' => $search,
                                                        'desa' => $desa,
                                                        'tahun' => $tahun,
                                                        'status_pengelolaan' => $status_pengelolaan,
                                                    ]) }}"
                                                        class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('kepemilikan.destroyPerLahan', [
                                                            'id_kepemilikan' => $k->id_kepemilikan,
                                                            'id_lahan' => $detail->lahan->id_lahan,
                                                        ]) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                            title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Mode normal --}}
                                                    <a href="{{ route('kepemilikan.show', [
                                                        'kepemilikan' => $k->id_kepemilikan,
                                                        'page' => request('page'),
                                                        'search' => $search,
                                                        'desa' => $desa,
                                                        'tahun' => $tahun,
                                                        'status_pengelolaan' => $status_pengelolaan,
                                                    ]) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <a href="{{ route('kepemilikan.edit', [
                                                        'kepemilikan' => $k->id_kepemilikan,
                                                        'page' => request('page'),
                                                        'search' => $search,
                                                        'desa' => $desa,
                                                        'tahun' => $tahun,
                                                        'status_pengelolaan' => $status_pengelolaan,
                                                    ]) }}"
                                                        class="btn btn-warning btn-sm">
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
                                                @endif
                                            </td>
                                            @php $firstRow = false; @endphp
                                        @endif
                                @endforeach
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Belum ada data kepemilikan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $kepemilikan->links('vendor.pagination.grouped') }}
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">Riwayat Lahan</label>
                            <select name="status_petani" id="filter_status_petani" class="form-select">
                                <option value="">Aktif</option> <!-- default -->
                                <option value="berhenti" {{ request('status_petani') == 'berhenti' ? 'selected' : '' }}>
                                    Berhenti</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Pengelolaan</label>
                            <select name="status_pengelolaan" id="filter_status"
                                class="form-select text-kecil choices-select">
                                <option value="">Semua Status</option>
                                <option value="KSM" {{ request('status_pengelolaan') == 'KSM' ? 'selected' : '' }}>KSM
                                </option>
                                <option value="Mandiri"
                                    {{ request('status_pengelolaan') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
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
            const statusSelect = document.getElementById('filter_status');
            if (desaSelect) new Choices(desaSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari desa..."
            });
            if (tahunSelect) new Choices(tahunSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari tahun..."
            });
            if (statusSelect) new Choices(statusSelect, {
                shouldSort: false,
                searchEnabled: false
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
