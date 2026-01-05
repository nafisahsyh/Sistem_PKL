@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Data Desa</h3>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-0">
                    {{-- Tombol Tambah --}}
                    <a href="{{ route('desa.create') }}" class="btn btn-success mb-3" title="Tambah Desa">
                        <i class="fas fa-plus"></i>
                    </a>
                    {{-- Form Search --}}
                    <form action="{{ route('desa.index') }}" method="GET" class="d-flex align-items-start mb-3">
                        <input type="text" name="search" class="form-control form-control-search me-2"
                            placeholder="Cari Desa/Kecamatan..." value="{{ request('search') }}" style="width: 300px;">
                        <button class="btn btn-success" type="submit" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('desa.index') }}" class="btn btn-primary ms-2" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Desa</th>
                            <th>Kecamatan</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($desa as $index => $item)
                            <tr>
                                <td class="text-center">
                                    {{ ($desa->currentPage() - 1) * $desa->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $item->desa }}</td>
                                <td>{{ $item->kecamatan->kecamatan ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('desa.edit', $item->id_desa) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('desa.destroy', $item->id_desa) }}" method="POST"
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
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Belum ada data desa</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $desa->links('vendor.pagination.grouped') }}
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
                                title: "<h3 style='font-size:15px;margin-bottom:2px;line-height:0.5;]'>Yakin ingin menghapus?</h3>",
                                html: "<p style='font-size:14px;margin:0;'>Data yang dihapus tidak dapat dikembalikan!</p>",
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
