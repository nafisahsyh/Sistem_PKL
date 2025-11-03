@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="icon" type="image/png" href="{{ asset('storage/img/logo.png') }}">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@section('content')
    <div class="container mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-success p-2" title="Kembali">
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
                        <table class="table table-bordered table-striped align-middle table-custom mb-0">
                            <thead class="text-center" style="background-color: #cce1d7; color: #014C2D;">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>Petani Sebelumnya</th>
                                    <th>Petani Sesudahnya</th>
                                    <th>Tanggal Ganti</th>
                                    <th>Keterangan</th>
                                    <th style="width: 10%">Aksi</th>
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
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-sm" title="Detail"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailPetaniModal{{ $item->id_riwayat }}">
                                                <i class="fas fa-eye"></i>
                                            </button>

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

@foreach ($riwayat as $item)
    <div class="modal fade" id="detailPetaniModal{{ $item->id_riwayat }}" tabindex="-1"
        aria-labelledby="detailPetaniLabel{{ $item->id_riwayat }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title" id="detailPetaniLabel{{ $item->id_riwayat }}">
                        Detail Petani
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-semibold text-success mb-3 border-bottom pb-1">Petani Sebelumnya</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th class="text-muted" width="40%">Nama</th>
                                    <td class="text-dark">: {{ $item->petaniSebelum->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">NIK</th>
                                    <td class="text-dark">: {{ $item->petaniSebelum->NIK ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Alamat</th>
                                    <td class="text-dark">: {{ $item->petaniSebelum->alamat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">No Telepon</th>
                                    <td class="text-dark">: {{ $item->petaniSebelum->no_telepon ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 class="fw-semibold text-success mb-3 border-bottom pb-1">Petani Sesudah</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th class="text-muted" width="40%">Nama</th>
                                    <td class="text-dark">: {{ $item->petaniSesudah->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">NIK</th>
                                    <td class="text-dark">: {{ $item->petaniSesudah->NIK ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Alamat</th>
                                    <td class="text-dark">: {{ $item->petaniSesudah->alamat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">No Telepon</th>
                                    <td class="text-dark">: {{ $item->petaniSesudah->no_telepon ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr class="my-3">

                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th class="text-muted" width="25%">Tanggal Ganti</th>
                            <td class="text-dark">: {{ \Carbon\Carbon::parse($item->tanggal_ganti)->format('d-m-Y') }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Keterangan</th>
                            <td class="text-dark">: {{ $item->keterangan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endforeach
