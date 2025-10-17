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

    <div class="row mt-4">
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
                    <a class="small text-white stretched-link" href="/pengguna">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

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
                        <i class="fas fa-tractor fa-2x"></i>
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


    </div>
</div>
@endsection
