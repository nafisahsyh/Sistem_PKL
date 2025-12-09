@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    @php
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $filters = [
            'desa' => request('id_desa'),
            'tahun' => request('id_tahun_tanam'),
            'tipe' => request('tipe'),
        ];

        $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();
    @endphp

    <div class="container-fluid px-4 mt-5">

        {{-- HEADER + FILTER BUTTON --}}
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="text-brown mb-0">Buku Besar</h3>

            <a href="{{ route('buku-besar.pdf') }}?
        tipe={{ request('tipe') }}
        &id_desa={{ request('id_desa') }}
        &id_tahun_tanam={{ request('id_tahun_tanam') }}
        &periode={{ request('periode') }}
        &tahun={{ request('tahun') }}
        &search={{ request('search') }}"
                target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>

        </div>


        {{-- TABLE CARD --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                {{-- FILTER BUTTON + SEARCH --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div class="d-flex gap-2">
                        {{-- Tombol Filter --}}
                        @php
                            $filters = [
                                'id_desa' => request('id_desa'),
                                'id_tahun_tanam' => request('id_tahun_tanam'),
                                'tipe' => request('tipe'),
                                'tahun' => request('tahun'), // 👈 tambah Tahun
                                'periode' => request('periode'),
                            ];
                            $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();
                        @endphp

                        <button
                            class="btn d-flex align-items-center gap-2 
            {{ $jumlahFilterAktif > 0 ? 'btn-success text-white' : 'btn-outline-success' }}"
                            data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                            @if ($jumlahFilterAktif > 0)
                                <span class="badge bg-warning text-dark">{{ $jumlahFilterAktif }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- FORM SEARCH --}}
                    <form action="{{ route('buku-besar.index') }}" method="GET"
                        class="d-flex align-items-start flex-wrap justify-content-end gap-2">

                        {{-- Keep filter desa & tahun tanam & tipe --}}
                        @foreach (['id_desa', 'id_tahun_tanam', 'tipe'] as $f)
                            @if (request()->filled($f))
                                <input type="hidden" name="{{ $f }}" value="{{ request($f) }}">
                            @endif
                        @endforeach

                        {{-- Search input --}}
                        <input type="text" name="search" class="form-control form-control-search"
                            placeholder="Cari No Plasma atau Nama Petani..." value="{{ request('search') }}"
                            style="width: 250px;">

                        <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                        <a href="{{ route('buku-besar.index') }}" class="btn btn-primary" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>Nama Petani</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Luasan (Ha)</th>
                            <th>Periode</th>
                            <th>Nominal</th>
                            @if (request('tipe', 'credit_bagihasil') == 'debit_pengambilan')
                                <th>Metode</th>
                            @else
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataTransaksi as $trx)
                            @php
                                $noPlasma = $trx->nomor_plasma ?? '-';
                                $namaPetani = $trx->nama_petani ?? '-';
                                $desaNama = $trx->nama_desa ?? '-';
                                $tahun = $trx->tahun_tanam ?? '-';
                                $luasan = $trx->luasan ?? '-';
                                $periode = $trx->periode_string ?? '-';
                                $totalNominal = $trx->total_nominal ?? 0;
                                $metode = $trx->metode ?? '-';
                            @endphp
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($dataTransaksi->currentPage() - 1) * $dataTransaksi->perPage() }}
                                </td>
                                <td class="text-center">{{ $noPlasma }}</td>
                                <td>{{ $namaPetani }}</td>
                                <td class="text-center">{{ $desaNama }}</td>
                                <td class="text-center">{{ $tahun }}</td>
                                <td class="text-center">
                                    {{ number_format($luasan, 2, ',', '.') }}
                                </td>
                                <td>{{ $periode }}</td>
                                <td class="text-end">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                                {{-- KOLM TERAKHIR KONDISIONAL --}}
                                @if (request('tipe') == 'debit_pengambilan')
                                    <td class="text-center">{{ ucfirst($metode) }}</td>
                                @else
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info btn-detail" data-id="{{ $trx->id_petani }}"
                                            data-awal="{{ $trx->bulan_awal }}" data-akhir="{{ $trx->bulan_akhir }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                    Belum ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $dataTransaksi->links('vendor.pagination.grouped') }}
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL FILTER --}}
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">

                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-filter me-2"></i> Filter Buku Besar
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('buku-besar.index') }}" method="GET">
                    <div class="modal-body px-4">
                        <div class="row g-2">

                            {{-- Desa --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Filter Desa</label>
                                <select name="id_desa" id="filter_desa" class="form-select">
                                    <option value="">Semua Desa</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}"
                                            {{ request('id_desa') == $d->id_desa ? 'selected' : '' }}>
                                            {{ $d->desa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tahun Tanam --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Filter Tahun Tanam</label>
                                <select name="id_tahun_tanam" id="filter_tahun_tanam" class="form-select">
                                    <option value="">Semua Tahun Tanam</option>
                                    @foreach ($tahunTanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}"
                                            {{ request('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                            {{ $t->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Periode --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Periode (2 Bulanan)</label>
                                <select name="periode" id="filter_periode" class="form-select">
                                    <option value="">Pilih Periode</option>
                                    <option value="1" {{ request('periode') == 1 ? 'selected' : '' }}>Januari-Februari
                                    </option>
                                    <option value="2" {{ request('periode') == 2 ? 'selected' : '' }}>Maret-April
                                    </option>
                                    <option value="3" {{ request('periode') == 3 ? 'selected' : '' }}>Mei-Juni
                                    </option>
                                    <option value="4" {{ request('periode') == 4 ? 'selected' : '' }}>Juli-Agustus
                                    </option>
                                    <option value="5" {{ request('periode') == 5 ? 'selected' : '' }}>
                                        September-Oktober</option>
                                    <option value="6" {{ request('periode') == 6 ? 'selected' : '' }}>
                                        November-Desember</option>
                                </select>
                            </div>

                            {{-- Tahun --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tahun</label>
                                <input type="number" name="tahun" class="form-control" placeholder="Misal: 2025"
                                    value="{{ request('tahun') }}" min="2000" max="3000">
                            </div>

                            {{-- Jenis Transaksi --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jenis Transaksi</label>
                                <select name="tipe" id="filter_tipe" class="form-select">
                                    <option value="credit_bagihasil"
                                        {{ request('tipe', 'credit_bagihasil') == 'credit_bagihasil' ? 'selected' : '' }}>
                                        Kredit</option>
                                    <option value="debit_pengambilan"
                                        {{ request('tipe') == 'debit_pengambilan' ? 'selected' : '' }}>Debit</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('buku-besar.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Terapkan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-sm border-0">

                <!-- Header -->
                <div class="modal-header bg-info rounded-top-4 border-0">
                    <h5 class="modal-title text-dark fw-bold">
                        <i class="fas fa-eye me-2"></i> Detail Transaksi Petani
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body" id="detailContent">
                    <div class="text-center py-2 text-muted">
                        <div class="spinner-border text-white" role="status" style="width: 3rem; height: 3rem;"></div>
                        <p class="mt-3 fs-6 text-white">Memuat data...</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>

                </div>

            </div>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const desaSelect = document.getElementById('filter_desa');
            const tahunSelect = document.getElementById('filter_tahun_tanam');
            const tipeSelect = document.getElementById('filter_tipe');
            const periodeSelect = document.getElementById('filter_periode');

            // Apply Choices
            if (desaSelect) new Choices(desaSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari desa..."
            });

            if (tahunSelect) new Choices(tahunSelect, {
                shouldSort: false,
                searchPlaceholderValue: "Cari tahun tanam..."
            });

            if (periodeSelect) new Choices(periodeSelect, {
                shouldSort: false,
                placeholder: true,
                placeholderValue: "Semua Periode",
                searchPlaceholderValue: "Cari periode...",
                itemSelectText: '',
                removeItemButton: false
            });

            if (tipeSelect) new Choices(tipeSelect, {
                shouldSort: false,
                searchEnabled: false,
                itemSelectText: '' // supaya klik option lebih bersih
            });

            // Reset button
            const btnReset = document.getElementById('resetFilter');
            if (btnReset) {
                btnReset.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = "{{ route('buku-besar.index') }}";
                });
            }
        });
    </script>

    <script>
        // ============================================================
        // 🔥 DETAIL MODAL HANDLER
        // ============================================================
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const awal = this.dataset.awal;
                const akhir = this.dataset.akhir;

                const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();

                document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-info"></div>
                <p class="mt-2">Memuat data...</p>
            </div>
        `;

                fetch(`/buku-besar/detail?id=${id}&awal=${awal}&akhir=${akhir}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('detailContent').innerHTML = html;
                    })
                    .catch(() => {
                        document.getElementById('detailContent').innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                        <p class="mt-2">Gagal memuat data</p>
                    </div>
                `;
                    });
            });
        });
    </script>
@endsection
