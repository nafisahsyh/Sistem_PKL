@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Data Kepemilikan</h3>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    {{-- Tombol Tambah --}}
                    <a href="{{ route('kepemilikan.create') }}" class="btn btn-success" title="Tambah Kepemilikan">
                        <i class="fas fa-plus"></i>
                    </a>

                    {{-- Form Search --}}
                    <form action="{{ route('kepemilikan.index') }}" method="GET" class="d-flex align-items-start">
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

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>No</th>
                            <th>Nomor Plasma</th>
                            <th>Nama Petani</th>
                            <th>Desa (Kecamatan)</th>
                            <th>Tahun Tanam</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kepemilikan as $k)
                            <tr>
                                <td class="text-center">
                                    {{ ($kepemilikan->currentPage() - 1) * $kepemilikan->perPage() + $loop->iteration }}
                                </td>

                                {{-- Nomor Plasma --}}
                                <td>{{ $k->petani->nomor_anggota_plasma ?? '-' }}</td>

                                {{-- Nama Petani --}}
                                <td>{{ $k->petani->nama ?? '-' }}</td>

                                {{-- Desa (Kecamatan) --}}
                                <td>
                                    @php
                                        $desaList = $k->detailKepemilikan
                                            ->pluck('lahan.desa.desa')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                        $kecamatanList = $k->detailKepemilikan
                                            ->pluck('lahan.desa.kecamatan.kecamatan')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                    @endphp
                                    {{ $desaList ?: '-' }} ({{ $kecamatanList ?: '-' }})
                                </td>

                                {{-- Tahun Tanam --}}
                                <td class="text-center">
                                    @php
                                        $tahunTanam = $k->detailKepemilikan
                                            ->pluck('lahan.tahunTanam.tahun')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                    @endphp
                                    {{ $tahunTanam ?: '-' }}
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    <span
                                        class="badge {{ $k->status_kepemilikan === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($k->status_kepemilikan) }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="text-center">
                                    <a href="{{ route('kepemilikan.show', $k->id_kepemilikan) }}"
                                        class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('kepemilikan.edit', $k->id_kepemilikan) }}"
                                        class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('kepemilikan.destroy', $k->id_kepemilikan) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
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

    {{-- Konfirmasi Hapus --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
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
