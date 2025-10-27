@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Data Kepemilikan</h3>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                {{-- 🔍 Search bar --}}
                <div class="d-flex justify-content-end mb-3">
                    <form action="{{ route('kepemilikan.index') }}" method="GET"
                        class="d-flex align-items-start flex-wrap justify-content-end">
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

                {{-- 🧾 Tabel Data --}}
                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>No</th>
                            <th>Nomor Plasma</th>
                            <th>Nama Petani</th>
                            <th>Status</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kepemilikan as $index => $k)
                            @php
                                $isSearching = request()->filled('search');

                                $detailList = $k->detailKepemilikan->map(
                                    fn($d) => [
                                        'desa' => $d->lahan->desa->desa ?? '-',
                                        'tahun' => $d->lahan->tahunTanam->tahun ?? '-',
                                        'id_lahan' => $d->id_lahan,
                                    ],
                                );

                                // Kalau tidak sedang search → hapus duplikat desa/tahun
                                if (!$isSearching) {
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
                                    {{-- Kolom utama (gabung baris kalau tidak search) --}}
                                    @if (!$isSearching && $i == 0)
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
                                                class="badge {{ $k->status_kepemilikan === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($k->status_kepemilikan) }}
                                            </span>
                                        </td>
                                    @elseif ($isSearching)
                                        {{-- Saat search, tampil semua kolom per baris --}}
                                        <td class="text-center align-middle">{{ $rowNumber }}</td>
                                        <td>{{ $k->petani->nomor_anggota_plasma ?? '-' }}</td>
                                        <td>{{ $k->petani->nama ?? '-' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $k->status_kepemilikan === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($k->status_kepemilikan) }}
                                            </span>
                                        </td>
                                    @endif

                                    {{-- Desa & Tahun --}}
                                    <td>{{ $detail['desa'] }}</td>
                                    <td class="text-center">{{ $detail['tahun'] }}</td>

                                    {{-- Aksi --}}
                                    @if (!$isSearching && $i == 0)
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
                                    @elseif ($isSearching)
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
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                        Belum ada data kepemilikan
                                    </p>
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

    {{-- 🧹 SweetAlert Konfirmasi Hapus --}}
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

    {{-- ✅ Alert Sukses --}}
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
