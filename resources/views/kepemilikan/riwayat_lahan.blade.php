@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="icon" type="image/png" href="{{ asset('storage/img/logo.png') }}">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')
    <div class="container mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            <a href="{{ route('kepemilikan.show', [
                'kepemilikan' => $id_kepemilikan,
                'page' => request('page'),
                'search' => request('search'),
                'desa' => request('desa'),
                'tahun' => request('tahun'),
                'status_pengelolaan' => request('status_pengelolaan'),
            ]) }}"
                class="btn btn-success p-2" title="Kembali">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            <h4 class="text-dark mb-0">Riwayat Kepemilikan Lahan</h4>
        </div>

        {{-- Info Lahan --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Informasi Lahan</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 table-sm">
                    <tbody>
                        <tr>
                            <th class="text-normal text-start ps-3" width="30%">Desa</th>
                            <td class="text-normal-sm text-start ps-3">{{ $lahan->desa->desa ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Kecamatan</th>
                            <td class="text-normal-sm text-start ps-3">{{ $lahan->desa->kecamatan->kecamatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Luas Lahan Berdasarkan Peta</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ number_format($lahan->luas_peta ?? 0, 2, ',', '.') }} m²
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel Riwayat Kepemilikan --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Riwayat Pergantian Petani</strong>
            </div>
            <div class="card-body p-0">
                @if ($riwayat->isEmpty())
                    <p class="text-center text-muted py-4 mb-0">Belum ada riwayat perpindahan kepemilikan lahan.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle table-custom mb-0">
                            <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>Petani Awal</th>
                                    <th>Petani Sekarang</th>
                                    <th>Tanggal Ganti</th>
                                    <th>Keterangan</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayat as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-normal-sm">{{ $item->petaniSebelum->nama ?? '-' }}</td>
                                        <td class="text-normal-sm">{{ $item->petaniSesudah->nama ?? '-' }}</td>
                                        <td class="text-center text-normal-sm">
                                            {{ \Carbon\Carbon::parse($item->tanggal_ganti)->format('d-m-Y') }}
                                        </td>
                                        <td class="text-normal-sm">{{ $item->keterangan ?? '-' }}</td>
                                        <td class="text-center d-flex justify-content-center gap-1">

                                            {{-- Tombol Detail --}}
                                            <button type="button" class="btn btn-info btn-sm" title="Detail"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailPetaniModal{{ $item->id_riwayat }}">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            {{-- Tombol Edit --}}
                                            <button type="button" class="btn btn-warning btn-sm" title="Edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRiwayatModal{{ $item->id_riwayat }}">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <form action="{{ route('riwayat.destroy', $item->id_riwayat) }}" method="POST"
                                                class="delete-confirm">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>


                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@php
    $firstRiwayatId = $riwayat->first()->id_riwayat ?? null;
@endphp

@foreach ($riwayat as $index => $item)
    @php $isFirst = $item->id_riwayat === $firstRiwayatId; @endphp

    {{-- Modal DETAIL --}}
    <div class="modal fade" id="detailPetaniModal{{ $item->id_riwayat }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">Detail Petani</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="row">
                        {{-- Petani Sebelum --}}
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-semibold text-success mb-3 border-bottom pb-1">
                                {{ $loop->last ? 'Petani Awal' : 'Petani Sebelumnya' }}
                            </h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th width="40%">Nama</th>
                                    <td>: {{ $item->petaniSebelum->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>NIK</th>
                                    <td>: {{ $item->petaniSebelum->NIK ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>: {{ $item->petaniSebelum->alamat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No Telepon</th>
                                    <td>: {{ $item->petaniSebelum->no_telepon ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        {{-- Petani Sesudah --}}
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-semibold text-success mb-3 border-bottom pb-1">
                                {{ $isFirst ? 'Petani Sekarang' : 'Petani Sesudah' }}
                            </h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th width="40%">Nama</th>
                                    <td>: {{ $item->petaniSesudah->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>NIK</th>
                                    <td>: {{ $item->petaniSesudah->NIK ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>: {{ $item->petaniSesudah->alamat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No Telepon</th>
                                    <td>: {{ $item->petaniSesudah->no_telepon ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th width="25%">Tanggal Ganti</th>
                            <td>: {{ \Carbon\Carbon::parse($item->tanggal_ganti)->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>: {{ $item->keterangan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal EDIT --}}
    <div class="modal fade" id="editRiwayatModal{{ $item->id_riwayat }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl d-flex align-items-center justify-content-center">

            <form action="{{ route('riwayat.update', $item->id_riwayat) }}" method="POST">
                @csrf @method('PUT')

                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header bg-warning text-black rounded-top-4">
                        <h5 class="modal-title">Edit Riwayat Kepemilikan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body px-4 py-3">

                        <div class="mb-3">
                            <label class="form-label">Tanggal Ganti</label>
                            <input type="date" name="tanggal_ganti" class="form-control"
                                value="{{ $item->tanggal_ganti }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control">{{ $item->keterangan }}</textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-warning">
                            <i class="fas fa-check me-1"></i>Perbarui</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.delete-confirm').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: "<h3 style='font-size:15px;margin-bottom:2px;line-height:0.5;'>Yakin menghapus riwayat?</h3>",
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
@endforeach
