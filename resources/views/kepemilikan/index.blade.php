@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="text-brown mb-0">Data Kepemilikan</h3>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCetakPDF">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </button>
        </div>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">

                {{-- 🔍 Filter & Search --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    {{-- Tombol Filter di kiri --}}
                    @php
                        $filters = [
                            'desa' => request('desa'),
                            'tahun' => request('tahun'),
                            'status_petani' => request('status_petani'),
                            'status_pengelolaan' => request('status_pengelolaan'),
                        ];

                        // Hitung jumlah filter aktif
                        $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();
                    @endphp


                    <button
                        class="btn d-flex align-items-center gap-2 
        {{ $jumlahFilterAktif > 0 ? 'btn-success text-white' : 'btn-outline-success' }}"
                        data-bs-toggle="modal" data-bs-target="#filterModal" title="Filter Data">
                        <i class="fas fa-filter"></i> Filter

                        @if ($jumlahFilterAktif > 0)
                            <span class="badge bg-warning text-dark">
                                {{ $jumlahFilterAktif }}
                            </span>
                        @endif
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
                        @if (request()->filled('status_petani'))
                            <input type="hidden" name="status_petani" value="{{ request('status_petani') }}">
                        @endif
                        @if (request()->filled('status_pengelolaan'))
                            <input type="hidden" name="status_pengelolaan" value="{{ request('status_pengelolaan') }}">
                        @endif

                        <input type="text" name="search" class="form-control form-control-search me-2"
                            placeholder="Cari Nama, No Plasma/Desa..." value="{{ request('search') }}"
                            style="width: 300px;">

                        <button class="btn btn-success" type="submit" title="Cari">
                            <i class="fas fa-search"></i>
                        </button>

                        <a href="{{ route('kepemilikan.index', request()->except('search')) }}" class="btn btn-primary ms-2"
                            title="Reset">
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
                <table class="table table-bordered align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>Nama Petani</th>
                            <th>Status Petani</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Kode</th>
                            <th>Status Kelola</th>
                            <th>Status Lahan</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php $rowIndex = 0; @endphp

@forelse ($kepemilikan as $index => $k)

    @if ($k->detailKepemilikan->isEmpty())
        @continue
    @endif

    @php
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

                            @foreach ($grouped as $group)
                                @foreach ($group as $i => $detail)
                                    <tr class="{{ $rowIndex % 2 == 0 ? 'odd-row' : 'even-row' }}">
                                        @php $rowIndex++; @endphp

                                        {{-- tampilkan kolom petani hanya di baris pertama --}}
                                        {{-- @if ($firstRow) --}}
                                        @if ($loop->first && $loop->parent->first)
                                            <td class="text-center align-middle"
                                                rowspan="{{ $k->detailKepemilikan->count() }}">
                                                {{ $rowNumber }}
                                            </td>
                                            <td class="text-center align-middle"
                                                rowspan="{{ $k->detailKepemilikan->count() }}">
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
                                        <td class="align-middle text-center striping">{{ $detail->kode_lahan ?? '-' }}</td>
                                        <td class="align-middle text-center striping">
                                            @if ($detail->status_pengelolaan === 'KSM')
                                                <span class="badge bg-success">KSM</span>
                                            @elseif ($detail->status_pengelolaan === 'Mandiri')
                                                <span class="badge bg-primary">Mandiri</span>
                                            @elseif ($detail->status_pengelolaan === 'Perusahaan')
                                                <span class="badge bg-warning text-dark">Perusahaan</span>
                                            @elseif (!empty($detail->status_pengelolaan))
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($detail->status_pengelolaan) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>

                                        @php
                                            $status = strtolower(trim($detail->status_kepemilikan));
                                        @endphp

                                        <td class="align-middle text-center striping">
                                            @if ($status === 'aktif')
                                                <span class="badge bg-success">Aktif</span>
                                            @elseif ($status === 'nonaktif' || $status === 'tidak aktif')
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            @elseif (!empty($status))
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($detail->status_kepemilikan) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>

                                        {{-- tombol aksi tampil sekali di baris pertama petani --}}
                                        {{-- @if ($firstRow) --}}
                                        @if ($loop->first && $loop->parent->first)
                                            @php
                                                // Ambil parameter request supaya aman digunakan
                                                $search = request('search');
                                                $desa = request('desa');
                                                $tahun = request('tahun');
                                                $status_pengelolaan = request('status_pengelolaan');

                                                // Deteksi apakah ada filter aktif
                                                $adaFilter =
                                                    filled($desa) || filled($tahun) || filled($status_pengelolaan);

                                                // Hitung jumlah lahan aktif di kepemilikan ini
                                                $jumlahLahan = $k->detailKepemilikan->count();

                                                // Tentukan apakah harus tampil per lahan
                                                // Sekarang termasuk juga kalau ada "search"
                                                $tampilPerLahan = false;

                                                if ($adaFilter) {
                                                    // Kalau ada filter, tampil per lahan
                                                    $tampilPerLahan = true;
                                                } elseif (!empty($search)) {
                                                    // Kalau search diisi, cek isinya
                                                    // Jika search mengandung angka 4 digit (tahun) atau cocok dengan salah satu nama desa, tampil per lahan
                                                    $mengandungTahun = preg_match('/\b(19|20)\d{2}\b/', $search);
                                                    $matchDesa = collect($daftarDesa ?? [])->contains(
                                                        fn($d) => stripos($d->desa, $search) !== false,
                                                    );

                                                    if ($mengandungTahun || $matchDesa) {
                                                        $tampilPerLahan = true;
                                                    } else {
                                                        $tampilPerLahan = false; // nama atau nomor plasma → normal
                                                    }
                                                }
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
                                                        'status_petani' => request('status_petani'),
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
                                                        'status_petani' => request('status_petani'),
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
                                                        'status_petani' => request('status_petani'),
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
                                                        'status_petani' => request('status_petani'),
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
                                            {{-- @php $firstRow = false; @endphp --}}
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
                            <label class="form-label fw-bold">Status Petani</label>
                            <select name="status_petani" id="filter_status_petani" class="form-select">
                                <option value="aktif" {{ request('status_petani') == 'aktif' ? 'selected' : '' }}>Aktif
                                </option>
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
                                <option value="Perusahaan"
                                    {{ request('status_pengelolaan') == 'Perusahaan' ? 'selected' : '' }}>Perusahaan
                                </option>
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

    <!-- ======== MODAL CETAK PDF ======== -->
    <div class="modal fade" id="modalCetakPDF" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered-custom modal-sm-custom">
            <div class="modal-content">

                <form action="{{ route('kepemilikan.cetakSemuaPDF') }}" method="GET" target="_blank">

                    <div class="modal-header" style="background-color: #dc3545">
                        <h5 class="modal-title" style="color:white">Pilih Kolom untuk Disembunyikan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        {{-- FILTER --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="desa" value="{{ request('desa') }}">
                        <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                        <input type="hidden" name="status_petani" value="{{ request('status_petani') }}">
                        <input type="hidden" name="status_pengelolaan" value="{{ request('status_pengelolaan') }}">

                        <div class="row">
                            <div class="col-6">
                                <label><input type="checkbox" name="exclude[]" value="nomor_plasma"> Nomor
                                    Plasma</label><br>
                                <label><input type="checkbox" name="exclude[]" value="nomor_koperasi"> Nomor
                                    Koperasi</label><br>
                                <label><input type="checkbox" name="exclude[]" value="nama"> Petani
                                    Sekarang</label><br>
                                <label><input type="checkbox" name="exclude[]" value="riwayat"> Petani
                                    Sebelum</label><br>
                                <label><input type="checkbox" name="exclude[]" value="desa"> Desa</label><br>
                                <label><input type="checkbox" name="exclude[]" value="tahun"> Tahun Tanam</label><br>
                            </div>

                            <div class="col-6">
                                <label><input type="checkbox" name="exclude[]" value="kode"> Kode Lahan</label><br>
                                <label><input type="checkbox" name="exclude[]" value="kavling"> Nomor Kavling</label><br>
                                <label><input type="checkbox" name="exclude[]" value="luas_peta"> Luas
                                    Lapangan</label><br>
                                <label><input type="checkbox" name="exclude[]" value="luas_surat"> Luas Surat</label><br>
                                <label><input type="checkbox" name="exclude[]" value="status_pengelolaan"> Status
                                    Kelola</label><br>
                                <label><input type="checkbox" name="exclude[]" value="status_kepemilikan"> Status
                                    Lahan</label><br>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">
                            Cetak PDF
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
            const riwayatSelect = document.getElementById('filter_status_petani');
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
            if (riwayatSelect) new Choices(riwayatSelect, {
                shouldSort: false,
                searchEnabled: false
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('resetFilter');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    // navigasi paksa ke route index tanpa query
                    window.location.href = "{{ route('kepemilikan.index') }}";
                });
            }
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
                        iconColor: '#dc3545',
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
