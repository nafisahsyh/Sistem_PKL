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
                            placeholder="Cari nama petani, nomor PBB, atau SHM..." value="{{ request('search') }}"
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
                            <th>Nama Petani</th>
                            <th>Nomor PBB</th>
                            <th>Luas Surat (m²)</th>
                            <th>Status Kepemilikan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kepemilikan as $k)
                            <tr>
                                <td class="text-center">
                                    {{ ($kepemilikan->currentPage() - 1) * $kepemilikan->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $k->petani->nama ?? '-' }}</td>
                                <td>{{ $k->nomor_pbb }}</td>
                                <td class="text-end">{{ number_format($k->luas_surat, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge {{ $k->status_kepemilikan === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($k->status_kepemilikan) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('kepemilikan.show', $k->id_kepemilikan) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('kepemilikan.edit', $k->id_kepemilikan) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('kepemilikan.destroy', $k->id_kepemilikan) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Belum ada data kepemilikan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

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
