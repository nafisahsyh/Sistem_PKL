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
                            <th class="text-normal text-start ps-3">Nomor Plasma</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Koperasi</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</td>
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

        @if ($totalLahan > 0)
            <div class="tab-slider-wrapper">

                {{-- tombol kiri --}}
                <button id="btnLeft" class="scroll-btn left" onclick="scrollTabs(-200)">
                    &#10094;
                </button>

                {{-- tab container --}}
                <div class="tab-slider" id="tabSlider">
                    <ul class="nav flex-nowrap gap-2 custom-tabs">

                        @foreach ($details as $index => $detail)
                            <li class="nav-item">
                                <button class="nav-link {{ $index == 0 ? 'active' : '' }}" data-bs-toggle="tab"
                                    data-bs-target="#lahan{{ $index }}">

                                    {{ $index + 1 }} {{ $detail->lahan->desa->desa ?? '-' }}
                                    {{ $detail->lahan->tahunTanam->tahun ?? '-' }}

                                </button>
                            </li>
                        @endforeach

                    </ul>
                </div>

                {{-- tombol kanan --}}
                <button id="btnRight" class="scroll-btn right" onclick="scrollTabs(200)">
                    &#10095;
                </button>

            </div>
        @endif
        <div class="tab-content mt-3">

            @foreach ($details as $index => $detail)
                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="lahan{{ $index }}">

                    <div class="card shadow-sm">

                        {{-- HEADER --}}
                        <div class="card-header bg-light text-success d-flex justify-content-between align-items-center">

                            <strong>
                                Lahan {{ $index + 1 }}
                            </strong>

                            {{-- RIWAYAT --}}
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

                        {{-- TABEL --}}
                        <div class="card-body p-0">
                            @include('kepemilikan._tabel_lahan_full', ['detail' => $detail])
                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

    <script>
        const slider = document.getElementById('tabSlider');
        const btnLeft = document.getElementById('btnLeft');
        const btnRight = document.getElementById('btnRight');

        function scrollTabs(amount) {
            slider.scrollBy({
                left: amount,
                behavior: 'smooth'
            });
        }

        function updateButtons() {
            const maxScroll = slider.scrollWidth - slider.clientWidth;

            if (slider.scrollLeft <= 0) {
                btnLeft.classList.add('hidden');
            } else {
                btnLeft.classList.remove('hidden');
            }

            if (slider.scrollLeft >= maxScroll - 5) {
                btnRight.classList.add('hidden');
            } else {
                btnRight.classList.remove('hidden');
            }
        }

        function centerActiveTab() {
            const activeTab = slider.querySelector('.nav-link.active');
            if (!activeTab) return;

            const tabRect = activeTab.getBoundingClientRect();
            const sliderRect = slider.getBoundingClientRect();

            const offset = tabRect.left - sliderRect.left - (sliderRect.width / 2) + (tabRect.width / 2);

            slider.scrollBy({
                left: offset,
                behavior: 'smooth'
            });
        }

        // event
        slider.addEventListener('scroll', updateButtons);
        window.addEventListener('load', () => {
            updateButtons();
            centerActiveTab();
        });
        window.addEventListener('resize', updateButtons);
    </script>
@endsection
