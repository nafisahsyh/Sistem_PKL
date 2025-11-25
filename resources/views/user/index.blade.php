@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Data Pengguna</h3>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    {{-- Tombol Tambah --}}
                    <a href="{{ route('user.create') }}" class="btn btn-success" title="Tambah User">
                        <i class="fas fa-plus"></i>
                    </a>

                    {{-- Form Search --}}
                    <form action="{{ route('user.index') }}" method="GET" class="d-flex align-items-start">
                        <input type="text" name="search" class="form-control form-control-search me-2"
                            placeholder="Cari nama, username, atau role..." value="{{ request('search') }}"
                            style="width: 300px;">
                        <button class="btn btn-success" type="submit" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('user.index') }}" class="btn btn-primary ms-2" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td class="text-center">
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $user->nama }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-left">{{ ucwords(str_replace('_', ' ', $user->role)) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('user.edit', $user->id_user) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('user.destroy', $user->id_user) }}" method="POST"
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
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Belum ada data user</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $users->links('vendor.pagination.grouped') }}
                </div>
            </div>
        </div>
    </div>

    {{-- SweetAlert --}}
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

    {{-- Notifikasi sukses & error --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: "Berhasil",
                text: "{{ session('success') }}",
                confirmButtonColor: '#198754',
                timer: 1800,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: "Gagal",
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc3545',
            });
        </script>
    @endif
@endsection