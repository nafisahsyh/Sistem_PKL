<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion bg-brown-custom" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                {{-- pemisahan dashboard sesuai role --}}
                <div class="sb-sidenav-menu-heading">Core</div>
                @php
                    $dashboardRoute =
                        Auth::user()->role === 'super_admin' ? route('dashboard.super') : route('dashboard.admin');
                @endphp
                {{-- Semua role bisa akses Dashboard --}}
                <a class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}" href="{{ $dashboardRoute }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-house"></i></div>
                    Dashboard
                </a>

                {{-- Bagian Interface --}}
                @if (in_array(Auth::user()->role, ['admin', 'super_admin']))
                    <div class="sb-sidenav-menu-heading">Interface</div>

                    {{-- Pengguna: hanya super admin --}}
                    @if (Auth::user()->role == 'super_admin')
                        <a class="nav-link {{ Request::is('user') ? 'active' : '' }}" href="/user">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Pengguna
                        </a>
                    @endif

                    {{-- Menu umum untuk admin & super admin --}}
                    <a class="nav-link {{ Request::is('kecamatan') ? 'active' : '' }}" href="/kecamatan">
                        <div class="sb-nav-link-icon"><i class="fas fa-map-marker-alt"></i></div>
                        Kecamatan
                    </a>

                    <a class="nav-link {{ Request::is('desa') ? 'active' : '' }}" href="/desa">
                        <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                        Desa
                    </a>

                    <a class="nav-link {{ Request::is('tahun_tanam') ? 'active' : '' }}" href="/tahun_tanam">
                        <div class="sb-nav-link-icon"><i class="fas fa-seedling"></i></div>
                        Tahun Tanam
                    </a>

                    <a class="nav-link {{ Request::is('petani') ? 'active' : '' }}" href="/petani">
                        <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                        Petani
                    </a>

                    <a class="nav-link {{ Request::is('kepemilikan') ? 'active' : '' }}" href="/kepemilikan">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-contract"></i></div>
                        Kepemilikan
                    </a>

                    {{-- Transaksi --}}
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapseTransaksi" aria-expanded="false" aria-controls="collapseTransaksi">
                        <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                        Transaksi
                    </a>
                    <div class="collapse" id="collapseTransaksi">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link" href="/pembelian">
                                <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                                Pemasukan
                            </a>
                            <a class="nav-link" href="/penjualan">
                                <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
                                Pengeluaran
                            </a>
                        </nav>
                    </div>

                    {{-- Laporan --}}
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapseLaporan" aria-expanded="false" aria-controls="collapseLaporan">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                        Laporan
                    </a>
                    <div class="collapse" id="collapseLaporan">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link" href="/laporan_pembelian">
                                <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                                Pemasukan
                            </a>
                            <a class="nav-link" href="/laporan_penjualan">
                                <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
                                Pengeluaran
                            </a>
                        </nav>
                    </div>
                @endif

            </div>
        </div>

        {{-- Footer tampil untuk semua role --}}
        <div class="sb-sidenav-footer">
            @if (Auth::check())
                <div class="small">Masuk sebagai:</div>
                {{ Auth::user()->nama }}
            @else
                <div class="small">Anda belum login</div>
            @endif
        </div>
    </nav>
</div>
