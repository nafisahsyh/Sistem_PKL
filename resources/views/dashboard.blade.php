@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">
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

        @if (in_array(Auth::user()->role, ['admin', 'super_admin']))
            <div class="row mt-4 g-2">

                @if (Auth::user()->role == 'super_admin')
                    <div class="col mb-3">
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

                <div class="col mb-3">
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

                <div class="col mb-3">
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

                <div class="col mb-3">
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

                <!-- Card Petani Aktif -->
                <div class="col mb-3">
                    <div class="card bg-success text-white">
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

                <!-- Card Petani Tidak Aktif -->
                <div class="col mb-3">
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

                <div class="col mb-3">
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
        @endif

        <hr class="mt-5 mb-4">

        <div class="card p-2 shadow-sm mb-4 w-100">
            <h6 class="text-center mb-2" style="font-size: 14px;">Luas Lahan per Desa & Tahun Tanam</h6>
            <canvas id="chartLahanHorizontal" style="width: 100%; height: 400px;"></canvas>
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
                        backgroundColor: 'rgba(2, 102, 60, 0.85)', // hijau transparan
                        borderColor: '#02663C', // hijau solid

                        borderWidth: 1
                    },
                    {
                        label: 'Luas Surat (m²)',
                        data: suratData,
                        backgroundColor: 'rgba(242, 201, 76, 0.85)', // kuning sawit lembut
                        borderColor: '#F2C94C', // kuning solid

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
