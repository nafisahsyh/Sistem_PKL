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
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapseAreaTanam" aria-expanded="false" aria-controls="collapseAreaTanam">
                        <div class="sb-nav-link-icon"><i class="fas fa-seedling"></i></div>
                        Area Tanam
                    </a>

                    <div class="collapse 
    {{ Request::is('kecamatan') || Request::is('desa') || Request::is('tahun_tanam') ? 'show' : '' }}"
                        id="collapseAreaTanam">

                        <nav class="sb-sidenav-menu-nested nav">

                            <a class="nav-link {{ Request::is('kecamatan') ? 'active' : '' }}" href="/kecamatan">
                                <div class="sb-nav-link-icon"><i class="fas fa-map-marker-alt"></i></div>
                                Kecamatan
                            </a>

                            <a class="nav-link {{ Request::is('desa') ? 'active' : '' }}" href="/desa">
                                <div class="sb-nav-link-icon"><i class="fas fa-map"></i></div>
                                Desa
                            </a>

                            <a class="nav-link {{ Request::is('tahun_tanam') ? 'active' : '' }}" href="/tahun_tanam">
                                <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                                Tahun Tanam
                            </a>

                        </nav>
                    </div>

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
                        <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                        Bagi Hasil KSM
                    </a>

                    <div class="collapse 
                        {{ request()->is('bagi-hasil-bulanan') ||
                        request()->is('penjualan') ||
                        request()->is('pengambilan-saldo*') ||
                        request()->is('inisiasi-saldo*')
                            ? 'show'
                            : '' }}"
                        id="collapseTransaksi">
                        <nav class="sb-sidenav-menu-nested nav">

                            <a class="nav-link {{ request()->is('bagi-hasil-bulanan') ? 'active' : '' }}"
                                href="/bagi-hasil-bulanan">
                                <div class="sb-nav-link-icon"><i class="fas fa-building"></i></div>
                                Bulanan
                            </a>
                            <a class="nav-link {{ request()->is('pengambilan-saldo*') ? 'active' : '' }}"
                                href="{{ route('pengambilan.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-people-arrows"></i></div>
                                Periode
                            </a>
                            <a class="nav-link {{ request()->is('inisiasi-saldo*') ? 'active' : '' }}"
                                href="{{ route('inisiasi-saldo.index') }}">
                                <div class="sb-nav-link-icon">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                Inisiasi Saldo
                            </a>
                        </nav>
                    </div>


                    {{-- Laporan --}}
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapseLaporan" aria-expanded="false" aria-controls="collapseLaporan">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-invoice"></i></div>
                        Laporan
                    </a>

                    <div class="collapse {{ request()->is('buku-besar*') || request()->is('saldo*') ? 'show' : '' }}"
                        id="collapseLaporan">
                        <nav class="sb-sidenav-menu-nested nav">

                            <a class="nav-link {{ request()->is('buku-besar*') ? 'active' : '' }}"
                                href="{{ route('buku-besar.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Buku Besar
                            </a>

                            <a class="nav-link {{ request()->is('saldo*') ? 'active' : '' }}"
                                href="{{ route('saldo.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                                Catatan Saldo
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
                <div class="fs-6">{{ Auth::user()->nama }}</div>
            @else
                <div class="small">Anda belum login</div>
            @endif
        </div>
    </nav>
</div>
