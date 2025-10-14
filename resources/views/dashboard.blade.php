@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
<div class="container-fluid px-4 mt-5">
    <h2 class="mt-4 text-brown">Dashboard Admin</h2>
    <h6>Selamat datang di <strong>Sistem Administrasi Plasma Koperasi Sawit Makmur</strong></h6>

    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-blue text-white mb-4">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-users"></i>
                    <span>Pengguna</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/penjualan">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-red text-white mb-4">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Kecamatan</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/penjualan">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-brown text-white mb-4">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-map"></i>
                    <span>Desa</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/penjualan">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-green text-white mb-4">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-tractor fa-lg"></i>
                    <span>Petani</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/penjualan">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>


    </div>
</div>
@endsection
