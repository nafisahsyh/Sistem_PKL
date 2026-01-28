@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Data Petani</h3>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    {{-- Tombol Tambah --}}
                    <a href="{{ route('petani.create') }}" class="btn btn-success" title="Tambah Petani">
                        <i class="fas fa-plus"></i>
                    </a>

                    {{-- Form Search --}}
                    <form action="{{ route('petani.index') }}" method="GET" class="d-flex align-items-start">
                        <input type="hidden" name="status"
                            value="{{ is_array(request('status')) ? implode(',', request('status')) : request('status') }}">
                        <input type="text" name="search" class="form-control form-control-search me-2"
                            placeholder="Cari Nama, NIK/No Plasma/Koperasi..." value="{{ request('search') }}"
                            style="width: 350px;">
                        <button class="btn btn-success" type="submit" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('petani.index', request('status') ? ['status' => request('status')] : []) }}"
                            class="btn btn-primary ms-2" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>No Koperasi</th>
                            <th>Nama Petani</th>
                            <th>NIK</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>KTP</th>
                            <th>KK</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($petani as $p)
                            <tr>
                                <td class="text-center">
                                    {{ ($petani->currentPage() - 1) * $petani->perPage() + $loop->iteration }}</td>
                                <td class="text-center">{{ $p->nomor_anggota_plasma ?? '—' }}</td>
                                <td class="text-center">{{ $p->nomor_anggota_koperasi ?: '—' }}</td>
                                <td>{{ $p->nama ?? '—' }}</td>
                                <td class="text-center">{{ $p->NIK ?: '—' }}</td>
                                <td class="text-center">
                                    @if ($p->no_telepon)
                                        @php
                                            // Buang tanda + supaya bisa diolah
                                            $nomor = ltrim($p->no_telepon, '+');

                                            // Tampilkan versi 0xxxx (misal +62812 → 0812)
                                            $tampil = '0' . substr($nomor, 2);
                                        @endphp

                                        <a href="https://wa.me/{{ $nomor }}" target="_blank">
                                            {{ $tampil }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        if ($p->status === 'aktif') {
                                            $label = 'Aktif';
                                            $badgeClass = 'bg-success';
                                        } elseif ($p->status === 'tidak_aktif') {
                                            $label = 'Tidak Aktif';
                                            $badgeClass = 'bg-secondary';
                                        } elseif ($p->status === 'berhenti') {
                                            $label = 'Berhenti';
                                            $badgeClass = 'bg-danger';
                                        } else {
                                            $label = ucfirst($p->status);
                                            $badgeClass = 'bg-secondary';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($p->pdf_scan_ktp)
                                        <a href="{{ asset('storage/ktp_pdf/' . $p->pdf_scan_ktp) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted"> - </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($p->pdf_scan_kk)
                                        <a href="{{ asset('storage/ktp_pdf/' . $p->pdf_scan_kk) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted"> - </span>
                                    @endif
                                </td>
                                @php
                                    // cek apakah petani punya lahan aktif
                                    $adaLahanAktif = $p
                                        ->kepemilikanAktif()
                                        ->whereHas('detailKepemilikan', function ($q) {
                                            $q->where('status_kepemilikan', 'aktif');
                                        })
                                        ->exists();
                                @endphp

                                @php
                                    // cek apakah petani punya lahan sama sekali (aktif / nonaktif)
                                    $punyaLahan = $p->detailKepemilikan()->exists();
                                @endphp

                                <td class="text-center" style="width: {{ $adaLahanAktif ? '125px' : '145px' }};">
                                    {{-- tombol tambah muncul hanya kalau belum punya lahan aktif dan petani tidak berhenti --}}
                                    @if (!$punyaLahan && $p->status === 'aktif')
                                        <a href="{{ route('petani.createkepemilikan', $p->id_petani) }}"
                                            class="btn btn-success btn-sm me-1" title="Tambah Kepemilikan">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('petani.edit', ['petani' => $p->id_petani, 'page' => $petani->currentPage()]) }}"
                                        class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('petani.destroy', $p->id_petani) }}" method="POST"
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
                                <td colspan="11" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Belum ada data petani</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $petani->links('vendor.pagination.grouped') }}
                </div>
            </div>
        </div>
    </div>

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
