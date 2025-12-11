@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            {{-- Tombol Kembali sebagai icon saja --}}
            <a href="{{ route('kepemilikan.index', [
                'page' => request('page'),
                'search' => request('search'),
                'desa' => request('desa'),
                'tahun' => request('tahun'),
                'status_pengelolaan' => request('status_pengelolaan'),
                'status_petani' => request('status_petani'),
            ]) }}"
                class="btn btn-success p-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            {{-- Judul --}}
            <h4 class="text-brown mb-0">Detail Kepemilikan</h4>

            {{-- Tombol aksi di kanan --}}
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('kepemilikan.pdf', $kepemilikan->id_kepemilikan) }}" class="btn btn-danger shadow-sm"
                    target="_blank">
                    <i class="fa fa-file-pdf"></i> Cetak PDF
                </a>
                <a href="{{ route('kepemilikan.edit', $kepemilikan->id_kepemilikan) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>


        {{-- Data Petani --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Data Petani</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 ">
                    <tbody>
                        <tr>
                            <th class="text-normal text-start ps-3" width="30%">Nama Lengkap</th>
                            <td class="text-normal-sm text-start ps-3" text-start ps-3>
                                {{ $kepemilikan->petani->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">NIK</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->NIK ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Telepon</th>
                            <td class="text-normal-sm text-start ps-3" text-start ps-3>
                                {{ $kepemilikan->petani->no_telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Koperasi</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Plasma</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Status Petani</th>
                            <td class="text-normal-sm text-start ps-3">
                                <span
                                    class="badge {{ $kepemilikan->petani->status == 'aktif'
                                        ? 'bg-success'
                                        : ($kepemilikan->petani->status == 'tidak_aktif'
                                            ? 'bg-secondary'
                                            : ($kepemilikan->petani->status == 'berhenti'
                                                ? 'bg-danger'
                                                : 'bg-secondary')) }}">
                                    {{ ucfirst($kepemilikan->petani->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Alamat</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Scan KTP</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($kepemilikan->petani->pdf_scan_ktp))
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}"
                                        target="_blank" class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada file</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th class="text-normal text-start ps-3">Scan KK</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($kepemilikan->petani->pdf_scan_kk))
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_kk) }}"
                                        target="_blank" class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada file</span>
                                @endif
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        @php
            // Urutkan lahan biar tidak loncat nomor
            $details = $kepemilikan->detailKepemilikan->sortBy('id_detail');
            $totalLahan = $details->count();
        @endphp

        {{-- LAHAN PERTAMA --}}
        @if ($totalLahan > 0)
            @php $detail = $details->first(); @endphp

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light text-success d-flex justify-content-between align-items-center">
                    <strong>Lahan 1</strong>

                    <a href="{{ route('kepemilikan.riwayatLahan', [
                        'id_lahan' => $detail->id_lahan,
                        'page' => request('page'),
                        'search' => request('search'),
                        'desa' => request('desa'),
                        'tahun' => request('tahun'),
                        'status_pengelolaan' => request('status_pengelolaan'),
                    ]) }}"
                        class="btn btn-info btn-sm text-dark">
                        <i class="fas fa-history"></i> Riwayat
                    </a>
                </div>

                <div class="card-body p-0">
                    @include('kepemilikan._tabel_lahan_full', ['detail' => $detail])
                </div>
            </div>
        @endif

        {{-- TOMBOL LIHAT/TUTUP --}}
        @if ($totalLahan > 1)
            <div class="d-flex justify-content-end my-3">
                <button id="toggleLahanBtn" class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#semuaLahan">
                    Lihat Semua Lahan ({{ $totalLahan - 1 }} lainnya)
                </button>
            </div>
        @endif


        {{-- COLLAPSE UNTUK SEMUA LAHAN --}}
        @if ($totalLahan > 1)
            <div id="semuaLahan" class="collapse">
                @foreach ($details->skip(1) as $index => $detail)
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light text-success d-flex justify-content-between align-items-center">
                            {{-- Nomor lahan sudah benar: 1 untuk lahan pertama, sisanya index+2 --}}
                            <strong>Lahan {{ $index + 1 }}</strong>

                            <a href="{{ route('kepemilikan.riwayatLahan', [
                                'id_lahan' => $detail->id_lahan,
                                'page' => request('page'),
                                'search' => request('search'),
                                'desa' => request('desa'),
                                'tahun' => request('tahun'),
                                'status_pengelolaan' => request('status_pengelolaan'),
                            ]) }}"
                                class="btn btn-info btn-sm text-dark">
                                <i class="fas fa-history"></i> Riwayat
                            </a>
                        </div>

                        <div class="card-body p-0">
                            @include('kepemilikan._tabel_lahan_full', ['detail' => $detail])
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var collapseEl = document.getElementById('semuaLahan');
            var btn = document.getElementById('toggleLahanBtn');

            collapseEl.addEventListener('show.bs.collapse', function() {
                btn.textContent = 'Sembunyikan Lahan';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-danger');
            });

            collapseEl.addEventListener('hide.bs.collapse', function() {
                btn.textContent = 'Lihat Semua Lahan ({{ $totalLahan - 1 }} lainnya)';
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-success');
            });
        });
    </script>
@endsection
