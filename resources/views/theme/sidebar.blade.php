<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion bg-brown-custom" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading">Core</div>

                <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="/dashboard">
                    <div class="sb-nav-link-icon"><i class="fas fa-house"></i></div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading">Interface</div>
                
                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="/users">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Pengguna
                </a>
            
                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="/brands">
                    <div class="sb-nav-link-icon"><i class="fas fa-map-marker-alt"></i></div>
                    Kecamatan
                </a>

                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="/categories">
                    <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                    Desa
                </a>

                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="/barang">
                    <div class="sb-nav-link-icon"><i class="fas fa-seedling"></i></div>
                    Tahun Tanam
                </a>

                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="/barang">
                    <div class="sb-nav-link-icon"><i class="fas fa-tractor"></i></div>
                    Petani
                </a>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTransaksi" aria-expanded="false" aria-controls="collapseTransaksi">
                    <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                    Transaksi
                </a>
                <div class="collapse" id="collapseTransaksi">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="/pembelian">
                            <div class="sb-nav-link-icon"><i class="fas fa-truck"></i></div>
                            Pembelian
                        </a>
                        <a class="nav-link" href="/penjualan">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Penjualan
                        </a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLaporan" aria-expanded="false" aria-controls="collapseLaporan">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                    Laporan
                </a>
                <div class="collapse" id="collapseLaporan">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="/laporan_pembelian">
                            <div class="sb-nav-link-icon"><i class="fas fa-truck"></i></div>
                            Pembelian
                        </a>
                        <a class="nav-link" href="/laporan_penjualan">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Penjualan
                        </a>
                    </nav>
                </div>

                <div class="sb-sidenav-footer">
                    @if (Auth::check())
                        <div class="small">Masuk sebagai:</div>
                        {{ Auth::user()->name }}
                    @else
                        <div class="small">Anda belum login</div>
                    @endif
                </div>

            </nav>
        </div>




