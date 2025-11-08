@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">

        <h3 class="mt-3 text-brown">
            @if (Auth::check() && Auth::user()->role === 'super_admin')
                Dashboard Super Admin
            @else
                Dashboard Admin
            @endif
        </h3>
        <h7>
            Selamat datang <strong>{{ Auth::user()->nama ?? 'Tamu' }}</strong> di
            <strong>Sistem Administrasi Plasma Koperasi Sawit Makmur</strong>
        </h7>

        {{-- CARD CARDS --}}
        @if (in_array(Auth::user()->role, ['admin', 'super_admin']))
            <div class="row mt-3 g-3 mb-2">
                {{-- ROW 1 --}}
                @if (Auth::user()->role == 'super_admin')
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card bg-blue text-white">
                            <div class="card-body d-flex align-items-center gap-2">
                                <i class="fas fa-users fa-2x"></i>
                                <div>
                                    <h6 class="mb-0">Pengguna</h6>
                                    <h3 class="mb-0">{{ $jumlahPengguna ?? 0 }}</h3>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a class="small text-white stretched-link" href="/user">Lihat Detail</a>
                                <i class="fas fa-angle-right small text-white"></i>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-green text-white">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                            <div>
                                <h6 class="mb-0">Kecamatan</h6>
                                <h3 class="mb-0">{{ $jumlahKecamatan ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a class="small text-white stretched-link" href="/kecamatan">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-red text-white">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="fas fa-map fa-2x"></i>
                            <div>
                                <h6 class="mb-0">Desa</h6>
                                <h3 class="mb-0">{{ $jumlahDesa ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a class="small text-white stretched-link" href="/desa">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-brown text-white">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="fas fa-leaf fa-2x"></i>
                            <div>
                                <h6 class="mb-0">Lahan</h6>
                                <h3 class="mb-0">{{ $jumlahLahan ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a class="small text-white stretched-link" href="/kepemilikan">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW 2 --}}
            <div class="row g-3">

                <div class="col-md-4 col-12 mb-3">
                    <div class="card bg-yellow text-white">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="fas fa-user fa-2x"></i>
                            <div>
                                <h6 class="mb-0">Petani</h6>
                                <h3 class="mb-0">{{ $jumlahPetani ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a class="small text-white stretched-link" href="/petani">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-6 mb-3">
                    <div class="card bg-pastel text-white">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="fas fa-user-check fa-2x"></i>
                            <div>
                                <h6 class="mb-0">Petani Aktif</h6>
                                <h3 class="mb-0">{{ $jumlahPetaniAktif ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a class="small text-white stretched-link" href="/petani?status=aktif">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-6 mb-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="fas fa-user-times fa-2x"></i>
                            <div>
                                <h6 class="mb-0">Petani Tidak Aktif</h6>
                                <h3 class="mb-0">{{ $jumlahPetaniNonaktif ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a class="small text-white stretched-link" href="/petani?status=tidak_aktif">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>

            </div>
        @endif


        <hr class="mt-4 mb-4">

        {{-- FILTER BUTTON --}}
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <button class="btn {{ $filterDesa || $filterTahun ? 'btn-success text-white' : 'btn-outline-success' }}"
                        data-bs-toggle="modal" data-bs-target="#modalFilterGrafik">
                        <i class="fas fa-filter me-1"></i> Filter Grafik
                    </button>

                    @if ($filterDesa || $filterTahun)
                        <a href="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-sync me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- GRAFIK --}}
        <div class="row mb-4">
            {{-- KIRI: BAR CHART --}}
            <div class="col-md-8">
                <div class="card p-2 shadow-sm w-100">
                    <h6 class="text-center mb-2" style="font-size: 14px;">Luas Lahan per Desa & Tahun Tanam</h6>
                    @if (count($chartData) == 0)
                        <p class="text-center text-muted my-3">Belum ada data untuk grafik.</p>
                    @else
                        <canvas id="chartLahanVertical" style="width: 100%; height: 420px;"></canvas>
                    @endif
                </div>
            </div>

            {{-- KANAN: PIE CHARTS (diperkecil supaya tinggi sesuai bar chart) --}}
            <div class="col-md-4 d-flex flex-column gap-3">
                <div class="card p-2 shadow-sm">
                    <h6 class="text-center mb-2" style="font-size: 14px;">Petani Berdasarkan Kelola</h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="chartPetaniPengelolaan"></canvas>
                    </div>
                </div>

                <div class="card p-2 shadow-sm">
                    <h6 class="text-center mb-2" style="font-size: 14px;">Jumlah Luasan Lahan</h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="chartLahanPengelolaan"></canvas>
                    </div>
                </div>
            </div>


            {{-- MODAL FILTER --}}
            <div class="modal fade" id="modalFilterGrafik" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="GET"
                        action="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                        class="modal-content rounded-4">

                        <div class="modal-header bg-success text-white rounded-top-4">
                            <h5 class="modal-title"><i class="fas fa-filter me-2"></i> Filter Grafik Lahan</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 py-3">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Desa</label>
                                <select name="desa" id="filter_desa" class="form-select">
                                    <option value="">Semua Desa</option>
                                    @foreach ($desaList as $desa)
                                        <option value="{{ $desa->id_desa }}"
                                            {{ $filterDesa == $desa->id_desa ? 'selected' : '' }}>
                                            {{ $desa->desa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tahun Tanam</label>
                                <select name="tahun" id="filter_tahun" class="form-select">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($tahunList as $tahun)
                                        <option value="{{ $tahun->id_tahun_tanam }}"
                                            {{ $filterTahun == $tahun->id_tahun_tanam ? 'selected' : '' }}>
                                            {{ $tahun->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <a href="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                                class="btn btn-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Terapkan
                            </button>
                        </div>


                    </form>
                </div>
            </div>


            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            {{-- SCRIPT GRAPH LAPANGAN / SURAT --}}
            @if (count($chartData) > 0)
                <script>
                    const chartLabels = @json(array_map(fn($row) => "{$row['desa']} ({$row['tahun']})", $chartData));
                    const lapanganData = @json(array_map(fn($row) => $row['lapangan'], $chartData));
                    const suratData = @json(array_map(fn($row) => $row['surat'], $chartData));

                    new Chart(document.getElementById('chartLahanVertical').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                    label: 'Luas Lapangan (m²)',
                                    data: lapanganData,
                                    backgroundColor: 'rgba(2, 102, 60, 0.85)',
                                    borderColor: '#02663C',
                                    borderWidth: 1,
                                },
                                {
                                    label: 'Luas Surat (m²)',
                                    data: suratData,
                                    backgroundColor: 'rgba(242, 201, 76, 0.85)',
                                    borderColor: '#F2C94C',
                                    borderWidth: 1,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 20
                                    }
                                }
                            }
                        }
                    });
                </script>
            @endif
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

            <script>
                const petaniMandiri = {{ $statusData['petani']['Mandiri'] }};
                const petaniKSM = {{ $statusData['petani']['KSM'] }};
                const lahanMandiri = {{ $statusData['lahan']['Mandiri'] }};
                const lahanKSM = {{ $statusData['lahan']['KSM'] }};

                // Tambahan luas surat & peta
                const mandiriSurat = {{ $statusData['lahan']['Mandiri_surat'] }};
                const mandiriPeta = {{ $statusData['lahan']['Mandiri_peta'] }};
                const ksmSurat = {{ $statusData['lahan']['KSM_surat'] }};
                const ksmPeta = {{ $statusData['lahan']['KSM_peta'] }};

                // --- PETANI PIE (tanpa luas) ---
                new Chart(document.getElementById('chartPetaniPengelolaan'), {
                    type: 'pie',
                    plugins: [ChartDataLabels],
                    data: {
                        labels: ['Mandiri', 'KSM'],
                        datasets: [{
                            data: [petaniMandiri, petaniKSM],
                            backgroundColor: ['#DB4125', '#02834E']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            },
                            datalabels: {
                                color: "#ffffff",
                                font: {
                                    weight: "bold",
                                    size: 14
                                },
                                formatter: (value) => value
                            }
                        }
                    }
                });

                // --- LAHAN PIE (dengan keterangan luas S & P) ---
                // --- LAHAN PIE (keterangan hanya saat hover) ---
                new Chart(document.getElementById('chartLahanPengelolaan'), {
                    type: 'pie',
                    plugins: [ChartDataLabels],
                    data: {
                        labels: ['Mandiri', 'KSM'],
                        datasets: [{
                            data: [lahanMandiri, lahanKSM],
                            backgroundColor: ['#DB4125', '#02834E']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            },
                            datalabels: {
                                color: "#ffffff",
                                font: {
                                    weight: "bold",
                                    size: 14
                                },
                                formatter: (value) => value // cuma angka jumlah
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const index = context.dataIndex;
                                        if (index === 0) {
                                            return [
                                                `Mandiri: ${lahanMandiri} lahan`,
                                                `Luas Surat: ${mandiriSurat} m²`,
                                                `Luas Peta: ${mandiriPeta} m²`
                                            ];
                                        } else {
                                            return [
                                                `KSM: ${lahanKSM} lahan`,
                                                `Luas Surat: ${ksmSurat} m²`,
                                                `Luas Peta: ${ksmPeta} m²`
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            </script>


            {{-- CHOICES SEARCH SELECT --}}
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
            <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const desaSelect = document.getElementById('filter_desa');
                    const tahunSelect = document.getElementById('filter_tahun');

                    if (desaSelect) new Choices(desaSelect, {
                        shouldSort: false,
                        searchPlaceholderValue: 'Cari desa...'
                    });
                    if (tahunSelect) new Choices(tahunSelect, {
                        shouldSort: false,
                        searchPlaceholderValue: 'Cari tahun...'
                    });
                });
            </script>

        @endsection
