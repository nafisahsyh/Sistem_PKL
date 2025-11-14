@extends('theme.default')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">


@section('content')
    <div class="container-fluid px-4 mt-5">

        <h3 class="mt-3" style="color: #014C2D;">
            @if (Auth::check() && Auth::user()->role === 'super_admin')
                Dashboard Super Admin
            @else
                Dashboard Admin
            @endif
        </h3>

        <h7>
            Selamat datang <span style="font-weight: 600; color: #014C2D;">{{ Auth::user()->nama ?? 'Tamu' }}</span> di
            <span style="font-weight: 600; color: #014C2D;">Sistem Administrasi Plasma Koperasi Sawit Makmur</span>
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
                            <a href="{{ url('/petani?status=aktif') }}" class="small text-white stretched-link">Lihat
                                Detail</a>
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
                            <a href="{{ url('/petani?status[]=tidak_aktif&status[]=berhenti') }}"
                                class="small text-white stretched-link">Lihat Detail</a>
                            <i class="fas fa-angle-right small text-white"></i>
                        </div>
                    </div>
                </div>

            </div>
        @endif


        <hr class="mt-4 mb-4">

        {{-- ===================== FILTER & GRAFIK LAHAN ===================== --}}
        <div class="row mb-0 align-items-center">
            <div class="col-md-8 d-flex justify-content-between align-items-center flex-wrap gap-2">
                {{-- Tombol Filter --}}
                <div>
                    <button
                        class="btn {{ $filterDesa || $filterTahun ? 'btn-success text-white' : 'btn-outline-success' }}"
                        data-bs-toggle="modal" data-bs-target="#modalFilterGrafik">
                        <i class="fas fa-filter me-1"></i> Filter Grafik Lahan
                    </button>

                    @if ($filterDesa || $filterTahun)
                        <a href="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-sync me-1"></i> Reset
                        </a>
                    @endif
                </div>

                {{-- Dropdown Tampilkan --}}
                <form method="GET"
                    action="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                    class="d-flex align-items-center gap-2">

                    <div style="margin-top:10px;">
                        <form method="GET"
                            action="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}">
                            <select name="limit" id="limit" class="form-select select-one" style="width: 130px;">
                                <option value="5" {{ request('limit') == 5 ? 'selected' : '' }}>5 data</option>
                                <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10 data</option>
                                <option value="all" {{ request('limit') == 'all' ? 'selected' : '' }}>Semua</option>
                            </select>
                        </form>
                    </div>


                    @if ($filterDesa)
                        <input type="hidden" name="desa" value="{{ $filterDesa }}">
                    @endif
                    @if ($filterTahun)
                        <input type="hidden" name="tahun" value="{{ $filterTahun }}">
                    @endif
                </form>

            </div>
        </div>


        {{-- ===================== GRAFIK LAHAN ===================== --}}
        <div class="row mb-4">
            {{-- KIRI: BAR CHART --}}
            <div class="col-md-8">
                <div class="card p-2 shadow-sm w-100">
                    <h6 class="text-center mb-2" style="font-size: 14px;">Luas Lahan per Desa & Tahun Tanam</h6>
                    {{-- Dropdown jumlah data --}}
                    @if (count($chartData) == 0)
                        <p class="text-center text-muted my-3">Belum ada data untuk grafik.</p>
                    @else
                        <canvas id="chartLahanVertical" style="width: 100%; height: 420px;"></canvas>
                    @endif
                </div>
            </div>

            {{-- KANAN: PIE CHARTS --}}
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
        </div>

        {{-- ===================== FILTER & GRAFIK PENGELOLAAN ===================== --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <button
                class="btn {{ request('filter_desa_pengelolaan') || request('filter_tahun_pengelolaan') ? 'btn-success text-white' : 'btn-outline-success' }}"
                data-bs-toggle="modal" data-bs-target="#modalFilterPengelolaan">
                <i class="fas fa-filter me-1"></i> Filter Grafik Kelola
            </button>

            @if (request('filter_desa_pengelolaan') || request('filter_tahun_pengelolaan'))
                <a href="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                    class="btn btn-outline-secondary">
                    <i class="fas fa-sync me-1"></i> Reset
                </a>
            @endif
        </div>

        {{-- GRAFIK STATUS KELOLA --}}
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card p-2 shadow-sm w-100">
                    <h6 class="text-center mb-2" style="font-size: 14px;">
                        Jumlah Petani & Lahan Berdasarkan Status Kelola per Desa & Tahun
                    </h6>
                    @if (count($pengelolaanChart) == 0)
                        <p class="text-center text-muted my-3">Belum ada data untuk grafik ini.</p>
                    @else
                        <canvas id="chartStatusKelola" style="width: 100%; height: 420px;"></canvas>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===================== MODAL FILTER LAHAN ===================== --}}
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

        {{-- ===================== MODAL FILTER PENGELOLAAN ===================== --}}
        <div class="modal fade" id="modalFilterPengelolaan" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form method="GET"
                    action="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}"
                    class="modal-content rounded-4">

                    <div class="modal-header bg-success text-white rounded-top-4">
                        <h5 class="modal-title"><i class="fas fa-filter me-2"></i> Filter Grafik Pengelolaan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Desa</label>
                            <select name="desa_kelola" id="desa_kelola" class="form-select">
                                <option value="">Semua Desa</option>
                                @foreach ($desaList as $desa)
                                    <option value="{{ $desa->id_desa }}"
                                        {{ request('desa_kelola') == $desa->id_desa ? 'selected' : '' }}>
                                        {{ $desa->desa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tahun Tanam</label>
                            <select name="tahun_kelola" id="tahun_kelola" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach ($tahunList as $tahun)
                                    <option value="{{ $tahun->id_tahun_tanam }}"
                                        {{ request('tahun_kelola') == $tahun->id_tahun_tanam ? 'selected' : '' }}>
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


        {{-- ==================== LIBRARY ==================== --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
        {{-- ==================== CHART 1: LUAS LAHAN ==================== --}}
        @if (count($chartData) > 0)
            <script>
                const chartLabels = @json(array_map(fn($row) => "{$row['desa']} ({$row['tahun']})", $chartData));
                const lapanganData = @json(array_map(fn($row) => $row['lapangan'], $chartData));
                const suratData = @json(array_map(fn($row) => $row['surat'], $chartData));

                new Chart(document.getElementById('chartLahanVertical'), {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                                label: 'Luas Lapangan (M²)',
                                data: lapanganData,
                                backgroundColor: 'rgba(2, 102, 60, 0.85)',
                                borderColor: '#02663C',
                                borderWidth: 1,
                                borderRadius: 5
                            },
                            {
                                label: 'Luas Surat (M²)',
                                data: suratData,
                                backgroundColor: 'rgba(242, 201, 76, 0.85)',
                                borderColor: '#F2C94C',
                                borderWidth: 1,
                                borderRadius: 5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 20
                            } // ➜ tambah ruang di atas
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            datalabels: {
                                color: '#000',
                                anchor: 'end',
                                align: 'top',
                                font: {
                                    size: 11
                                },
                                formatter: value => value.toLocaleString(),
                                clip: false // ➜ biar label gak kepotong
                            }
                        },
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
                    },
                    plugins: [ChartDataLabels]
                });
            </script>
        @endif

        <script>
            const petaniMandiri = {{ $statusData['petani']['Mandiri'] }};
            const petaniKSM = {{ $statusData['petani']['KSM'] }};
            const lahanMandiri = {{ $statusData['lahan']['Mandiri'] }};
            const lahanKSM = {{ $statusData['lahan']['KSM'] }};

            const mandiriSurat = {{ $statusData['lahan']['Mandiri_surat'] }};
            const mandiriPeta = {{ $statusData['lahan']['Mandiri_peta'] }};
            const ksmSurat = {{ $statusData['lahan']['KSM_surat'] }};
            const ksmPeta = {{ $statusData['lahan']['KSM_peta'] }};

            // ============= PETANI PIE =============
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
                            formatter: (value) => value.toLocaleString()
                        }
                    }
                }
            });

            // ============= LAHAN PIE (hover baru muncul detail) =============
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
                            formatter: (value) => value.toLocaleString()
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    if (index === 0) {
                                        return [
                                            `Mandiri: ${lahanMandiri.toLocaleString()} lahan`,
                                            `Luas Surat: ${mandiriSurat.toLocaleString()} m²`,
                                            `Luas Peta: ${mandiriPeta.toLocaleString()} m²`
                                        ];
                                    } else {
                                        return [
                                            `KSM: ${lahanKSM.toLocaleString()} lahan`,
                                            `Luas Surat: ${ksmSurat.toLocaleString()} m²`,
                                            `Luas Peta: ${ksmPeta.toLocaleString()} m²`
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            });
        </script>
        {{-- ==================== CHART 2: STATUS KELOLA ==================== --}}
        @if (count($pengelolaanChart) > 0)
            <script>
                const chartKelolaData = @json($pengelolaanChart);
                const kelolaLabels = chartKelolaData.map(row => `${row.desa} (${row.tahun})`);
                const ksmLahan = chartKelolaData.map(row => row.lahan_ksm);
                const mandiriLahan = chartKelolaData.map(row => row.lahan_mandiri);

                new Chart(document.getElementById('chartStatusKelola'), {
                    type: 'bar',
                    data: {
                        labels: kelolaLabels,
                        datasets: [{
                                label: 'Lahan KSM',
                                data: ksmLahan,
                                backgroundColor: 'rgba(24, 115, 235, 0.8)',
                                borderRadius: 5
                            },
                            {
                                label: 'Lahan Mandiri',
                                data: mandiriLahan,
                                backgroundColor: 'rgba(242, 201, 76, 0.85)',
                                borderRadius: 5
                            }
                        ]
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 20,
                                    color: '#333'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                padding: 10,
                                callbacks: {
                                    title: ctx => `${ctx[0].label}`,
                                    label: ctx => {
                                        const index = ctx.dataIndex;
                                        const row = chartKelolaData[index];

                                        // Format tooltip lebih rapi
                                        const petaniKSM = row.petani_ksm.toLocaleString();
                                        const petaniMandiri = row.petani_mandiri.toLocaleString();
                                        const luasSuratKSM = row.luas_surat_ksm.toLocaleString();
                                        const luasSuratMandiri = row.luas_surat_mandiri.toLocaleString();
                                        const luasPetaKSM = row.luas_peta_ksm.toLocaleString();
                                        const luasPetaMandiri = row.luas_peta_mandiri.toLocaleString();

                                        return [
                                            `Petani KSM             : ${petaniKSM}`,
                                            `Petani Mandiri         : ${petaniMandiri}`,
                                            `Luas Surat KSM      : ${luasSuratKSM} m²`,
                                            `Luas Surat Mandiri  : ${luasSuratMandiri} m²`,
                                            `Luas Peta KSM       : ${luasPetaKSM} m²`,
                                            `Luas Peta Mandiri   : ${luasPetaMandiri} m²`
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Jumlah Lahan',
                                    font: {
                                        size: 13,
                                        weight: 'bold'
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        @endif

        {{-- CHOICES --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Aktifkan Choices.js untuk semua filter
                ['filter_desa', 'filter_tahun', 'desa_kelola', 'tahun_kelola'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) new Choices(el, {
                        shouldSort: false,
                        searchPlaceholderValue: 'Cari...'
                    });
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const limitSelect = document.getElementById('limit');

                const choicesLimit = new Choices(limitSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false
                });

                limitSelect.addEventListener('change', function() {
                    limitSelect.form.submit();
                });
            });
        </script>
    @endsection
