@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-success p-2" title="Kembali">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>
            <h4 class="text-brown mb-0">Riwayat Kepemilikan Lahan</h4>
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
                                {{ number_format($lahan->luas_peta ?? 0, 2, ',', '.') }} m²</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel Riwayat --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <strong>Riwayat Pergantian Petani</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-normal text-center" width="5%">No</th>
                            <th class="text-normal text-center">Tanggal Ganti</th>
                            <th class="text-normal text-start ps-3">Petani Awal</th>
                            <th class="text-normal text-start ps-3">Petani Sekarang</th>
                            <th class="text-normal text-start ps-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $index => $r)
                            <tr>
                                <td class="text-normal-sm text-center">{{ $index + 1 }}</td>
                                <td class="text-normal-sm text-center">
                                    {{ \Carbon\Carbon::parse($r->tanggal_ganti)->format('d-m-Y') }}</td>
                                <td class="text-normal-sm text-start ps-3">{{ $r->petaniSebelum->nama ?? '-' }}</td>
                                <td class="text-normal-sm text-start ps-3">{{ $r->petaniSesudah->nama ?? '-' }}</td>
                                <td class="text-normal-sm text-start ps-3">
                                    <span class="text-normal-sm">{{ $r->keterangan ?? '-' }}</span>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat kepemilikan untuk
                                    lahan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endsection
