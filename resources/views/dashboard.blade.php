@extends('theme.default')

@section('content')
<div class="container-fluid px-4 mt-5">
    <h2 class="mt-4 text-brown">Dashboard Admin</h2>
    <h6>Selamat datang di <strong>Sistem Administrasi Plasma Koperasi Sawit Makmur</strong></h6>

    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-brown-custom text-white mb-4">
                <div class="card-body">Total Barang</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/barang">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">Transaksi Hari Ini</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/penjualan">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
