@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h2 class="mt-4 text-brown">
            @if (Auth::check() && Auth::user()->role === 'super_admin')
                Dashboard Super Admin
            @else
                Dashboard Admin
            @endif
        </h2>
        <h6>
            Selamat datang <strong>{{ Auth::user()->nama ?? 'Tamu' }}</strong> di
            <strong>Sistem Administrasi Plasma Koperasi Sawit Makmur</strong>
        </h6>

        @if (in_array(Auth::user()->role, ['admin', 'super_admin']))
            <div class="row mt-4">
                @if (Auth::user()->role == 'super_admin')
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-blue text-white mb-4">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-users fa-2x"></i>
                                    <div>
                                        <h6 class="mb-0">Pengguna</h6>
                                        <h3 class="mb-0">
                                            @isset($jumlahPengguna)
                                                {{ $jumlahPengguna }}
                                            @else
                                                0
                                            @endisset
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="/user">Lihat Detail</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-red text-white mb-4">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-map-marker-alt fa-2x"></i>
                                <div>
                                    <h6 class="mb-0">Kecamatan</h6>
                                    <h3 class="mb-0">
                                        @isset($jumlahKecamatan)
                                            {{ $jumlahKecamatan }}
                                        @else
                                            0
                                        @endisset
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="/kecamatan">Lihat Detail</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-brown text-white mb-4">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-map fa-2x"></i>
                                <div>
                                    <h6 class="mb-0">Desa</h6>
                                    <h3 class="mb-0">
                                        @isset($jumlahDesa)
                                            {{ $jumlahDesa }}
                                        @else
                                            0
                                        @endisset
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="/desa">Lihat Detail</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-green text-white mb-4">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-user fa-2x"></i>
                                <div>
                                    <h6 class="mb-0">Petani</h6>
                                    <h3 class="mb-0">
                                        @isset($jumlahPetani)
                                            {{ $jumlahPetani }}
                                        @else
                                            0
                                        @endisset
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="/petani">Lihat Detail</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
        @endif

        <hr class="mt-5 mb-4">

        <div class="card p-2 shadow-sm mb-4" style="max-width: 700px; margin: auto;">
            <h6 class="text-center mb-2" style="font-size: 14px;">Luas Lahan per Desa & Tahun Tanam</h6>
            <canvas id="chartLahanHorizontal" height="400"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartLabels = @json(array_map(function ($row) {
                return $row['desa'] . ' (' . $row['tahun'] . ')';
            }, $chartData));

        const lapanganData = @json(array_map(function ($row) {
                return $row['lapangan'];
            }, $chartData));

        const suratData = @json(array_map(function ($row) {
                return $row['surat'];
            }, $chartData));

        const ctx = document.getElementById('chartLahanHorizontal').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                        label: 'Luas Lapangan (m²)',
                        data: lapanganData,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Luas Surat (m²)',
                        data: suratData,
                        backgroundColor: 'rgba(255, 159, 64, 0.6)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                indexAxis: 'y', // horizontal bar
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Luas (m²)'
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
