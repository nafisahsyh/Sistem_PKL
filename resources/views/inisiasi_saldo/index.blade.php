@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">
        <h3 class="mt-4 text-brown">Inisiasi Saldo Awal</h3>

        <div class="card shadow-sm rounded-3">
            <div class="card-body">

                {{-- TOP BAR --}}
                <div class="d-flex align-items-center mb-3 justify-content-between flex-wrap">
                    {{-- FILTER --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @php
                            $filters = [
                                'id_desa' => request('id_desa'),
                                'id_tahun_tanam' => request('id_tahun_tanam'),
                                'search' => request('search'),
                            ];
                            $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();
                        @endphp

                        {{-- BUTTON INIT DATA --}}
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalInitData">
                            <i class="fas fa-plus"></i>
                        </button>

                        {{-- FILTER --}}
                        <button
                            class="btn d-flex align-items-center gap-2 
        {{ $jumlahFilterAktif > 0 ? 'btn-success text-white' : 'btn-outline-success' }}"
                            data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                            @if ($jumlahFilterAktif > 0)
                                <span class="badge bg-warning text-dark">{{ $jumlahFilterAktif }}</span>
                            @endif
                        </button>

                    </div>


                    {{-- SEARCH --}}
                    <div>
                        <form action="{{ route('inisiasi-saldo.index') }}" method="GET" class="d-flex gap-2">
                            @foreach (['id_desa', 'id_tahun_tanam'] as $f)
                                @if (request()->filled($f))
                                    <input type="hidden" name="{{ $f }}" value="{{ request($f) }}">
                                @endif
                            @endforeach

                            <input type="text" name="search" class="form-control"
                                placeholder="Cari Nama/No Plasma/Koperasi..." value="{{ request('search') }}"
                                style="width: 300px;">
                            <button class="btn btn-success"><i class="fas fa-search"></i></button>
                            <a href="{{ route('inisiasi-saldo.index', [
                                'id_desa' => request('id_desa'),
                                'id_tahun_tanam' => request('id_tahun_tanam'),
                            ]) }}"
                                class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        </form>
                    </div>
                </div>

                {{-- INFO --}}
                <div class="alert alert-warning small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Saldo awal hanya diinput <b>satu kali</b> sebelum sistem keuangan berjalan.
                </div>

                {{-- TABLE --}}
                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7;color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>No Koperasi</th>
                            <th>Nama Petani</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Saldo Awal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data as $row)
                            @php
                                $saldoAwal = \App\Models\Saldo::where('id_petani', $row->id_petani)
                                    ->where('id_desa', $row->id_desa)
                                    ->where('id_tahun_tanam', $row->id_tahun_tanam)
                                    ->where('bulan_awal', '2024-11')
                                    ->where('bulan_akhir', '2024-12')
                                    ->first();
                            @endphp

                            <tr>
                                <td class="text-center">
                                    {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                </td>
                                <td class="text-center">{{ $row->nomor_plasma }}</td>
                                <td class="text-center">{{ $row->nomor_koperasi }}</td>
                                <td>{{ $row->nama_petani }}</td>
                                <td class="text-center">{{ $row->desa }}</td>
                                <td class="text-center">{{ $row->tahun_tanam }}</td>

                                <td class="{{ $saldoAwal ? 'text-end' : 'text-center' }}">
                                    {{ $saldoAwal ? 'Rp ' . number_format($saldoAwal->saldo_awal, 2, ',', '.') : '-' }}
                                </td>

                                <td class="text-center">
                                    @if ($saldoAwal)
                                        <span class="badge bg-success">Saldo Awal</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Ada</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($sistemBerjalan)
                                        <span class="badge bg-warning text-black">Terkunci</span>
                                    @elseif (!$saldoAwal)
                                        <button class="btn btn-success btn-sm btn-init-saldo" data-bs-toggle="modal"
                                            data-bs-target="#modalSaldoAwal" data-id_petani="{{ $row->id_petani }}"
                                            data-id_desa="{{ $row->id_desa }}"
                                            data-id_tahun_tanam="{{ $row->id_tahun_tanam }}"
                                            data-nama="{{ $row->nama_petani }}" data-plasma="{{ $row->nomor_plasma }}"
                                            data-koperasi="{{ $row->nomor_koperasi }}" data-desa="{{ $row->desa }}"
                                            data-tahun="{{ $row->tahun_tanam }}">
                                            <i class="fas fa-plus" style="transform:none; vertical-align:middle;"></i>
                                        </button>
                                    @else
                                        <div class="btn-group btn-group-sm gap-1">
                                            {{-- EDIT --}}
                                            <button class="btn btn-warning btn-edit-saldo" data-bs-toggle="modal"
                                                data-bs-target="#modalEditSaldo" data-id_saldo="{{ $saldoAwal->id_saldo }}"
                                                data-saldo="{{ $saldoAwal->saldo }}"
                                                data-plasma="{{ $row->nomor_plasma }}"
                                                data-koperasi="{{ $row->nomor_koperasi }}"
                                                data-nama="{{ $row->nama_petani }}" data-desa="{{ $row->desa }}"
                                                data-tahun="{{ $row->tahun_tanam }}">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- HAPUS --}}
                                            <form action="{{ route('inisiasi-saldo.destroy', $saldoAwal->id_saldo) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                    Data petani tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $data->links('vendor.pagination.grouped') }}
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL FILTER --}}
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">

                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-filter me-2"></i> Filter Inisiasi Saldo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('inisiasi-saldo.index') }}" method="GET">
                    <div class="modal-body px-4">
                        <div class="row g-3">
                            {{-- Desa --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Desa</label>
                                <select name="id_desa" id="filter_desa" class="form-select">
                                    <option value="">Semua Desa</option>
                                    @foreach ($desa as $d)
                                        <option value="{{ $d->id_desa }}"
                                            {{ request('id_desa') == $d->id_desa ? 'selected' : '' }}>
                                            {{ $d->desa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tahun Tanam --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Tahun Tanam</label>
                                <select name="id_tahun_tanam" id="filter_tahun_tanam" class="form-select">
                                    <option value="">Semua Tahun Tanam</option>
                                    @foreach ($tahunTanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}"
                                            {{ request('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                            {{ $t->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('inisiasi-saldo.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Terapkan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- MODAL INISIASI SALDO --}}
    <div class="modal fade" id="modalSaldoAwal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">

                <form action="{{ route('inisiasi-saldo.store') }}" method="POST">
                    @csrf

                    {{-- HEADER --}}
                    <div class="modal-header bg-success text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="fas fa-wallet me-2"></i> Inisiasi Saldo Awal
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    {{-- BODY --}}
                    <div class="modal-body px-4">

                        {{-- INFO PETANI --}}
                        <div class="alert alert-light border small mb-3">
                            <div><b>No Plasma:</b> <span id="m_plasma"></span></div>
                            <div><b>No Koperasi:</b> <span id="m_koperasi"></span></div>
                            <div><b>Nama:</b> <span id="m_nama"></span></div>
                            <div><b>Desa:</b> <span id="m_desa"></span></div>
                            <div><b>Tahun Tanam:</b> <span id="m_tahun"></span></div>
                        </div>

                        {{-- HIDDEN --}}
                        <input type="hidden" name="id_petani" id="m_id_petani">
                        <input type="hidden" name="id_desa" id="m_id_desa">
                        <input type="hidden" name="id_tahun_tanam" id="m_id_tahun_tanam">

                        {{-- SALDO --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Saldo Awal</label>
                            <div class="input-group">
                                <span class="input-group-text rp-addon">Rp</span>
                                <input type="text" name="saldo" id="saldo" class="form-control text-end"
                                    placeholder="0" required>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH DATA PETANI (MASSAL) --}}
    <div class="modal fade" id="modalInitData" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">

                {{-- HEADER --}}
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i> Buat Data Inisiasi Saldo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- FORM --}}
                <form action="{{ route('inisiasi-saldo.storePetani') }}" method="POST">
                    @csrf

                    {{-- BODY --}}
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Desa</label>
                            <select name="id_desa" id="tambah_desa" class="form-select" required>
                                <option value="">Semua Desa</option>
                                @foreach ($desa as $d)
                                    <option value="{{ $d->id_desa }}">{{ $d->desa }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tahun Tanam</label>
                            <select name="id_tahun_tanam" id="tambah_tahun_tanam" class="form-select" required>
                                <option value="">Semua Tahun Tanam</option>
                                @foreach ($tahunTanam as $t)
                                    <option value="{{ $t->id_tahun_tanam }}">{{ $t->tahun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="alert alert-warning small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Sistem akan membuat data <b>inisiasi saldo</b> untuk
                            <b>seluruh petani</b> pada desa dan tahun tanam ini.
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i></i> Buat Data
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT SALDO AWAL --}}
    <div class="modal fade" id="modalEditSaldo" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">

                <form method="POST" id="formEditSaldo">
                    @csrf
                    @method('PUT')

                    {{-- HEADER --}}
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="fas fa-wallet me-2"></i> Edit Saldo Awal
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    {{-- BODY --}}
                    <div class="modal-body px-4">

                        {{-- INFO PETANI (OPSIONAL, JIKA MAU SAMA PERSIS) --}}
                        <div class="alert alert-light border small mb-3">
                            <div><b>No Plasma:</b> <span id="e_plasma"></span></div>
                            <div><b>No Koperasi:</b> <span id="e_koperasi"></span></div>
                            <div><b>Nama:</b> <span id="e_nama"></span></div>
                            <div><b>Desa:</b> <span id="e_desa"></span></div>
                            <div><b>Tahun Tanam:</b> <span id="e_tahun"></span></div>
                        </div>

                        {{-- HIDDEN --}}
                        <input type="hidden" name="id_saldo" id="e_id_saldo">

                        {{-- SALDO --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Saldo Awal</label>
                            <div class="input-group">
                                <span class="input-group-text rp-addon">Rp</span>
                                <input type="text" name="saldo" id="editSaldo" class="form-control text-end"
                                    placeholder="0" required>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Perbarui
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const desaSelect = document.getElementById('filter_desa');
            const tahunSelect = document.getElementById('filter_tahun_tanam');
            const pilihDesa = document.getElementById('tambah_desa');
            const pilihTahun = document.getElementById('tambah_tahun_tanam');

            if (desaSelect) {
                new Choices(desaSelect, {
                    shouldSort: false,
                    searchPlaceholderValue: "Cari desa..."
                });
            }

            if (tahunSelect) {
                new Choices(tahunSelect, {
                    shouldSort: false,
                    searchPlaceholderValue: "Cari tahun tanam..."
                });
            }

            if (pilihDesa) {
                new Choices(pilihDesa, {
                    shouldSort: false,
                    searchPlaceholderValue: "Cari desa..."
                });
            }

            if (pilihTahun) {
                new Choices(pilihTahun, {
                    shouldSort: false,
                    searchPlaceholderValue: "Cari tahun tanam..."
                });
            }

            const editSaldoInput = document.getElementById('editSaldo');

            if (editSaldoInput) {
                editSaldoInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, ''); // hapus semua selain angka
                    e.target.value = formatRupiah(value);
                });

                function formatRupiah(angka) {
                    if (!angka) return '';
                    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
            }

        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.delete-form');

                Swal.fire({
                    title: "<h3 style='font-size:15px;margin-bottom:2px;'>Yakin ingin menghapus?</h3>",
                    html: "<p style='font-size:14px;margin:0;'>Data yang dihapus tidak dapat dikembalikan!</p>",
                    icon: 'warning',
                    iconColor: '#dc3545',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.btn-edit-saldo').forEach(btn => {
            btn.addEventListener('click', function() {

                const id = this.dataset.id_saldo;
                const saldo = this.dataset.saldo;

                const form = document.getElementById('formEditSaldo');
                form.action = `/inisiasi-saldo/${id}`;

                document.getElementById('e_plasma').textContent = this.dataset.plasma;
                document.getElementById('e_koperasi').textContent = this.dataset.koperasi;
                document.getElementById('e_nama').textContent = this.dataset.nama;
                document.getElementById('e_desa').textContent = this.dataset.desa;
                document.getElementById('e_tahun').textContent = this.dataset.tahun;

                document.getElementById('editSaldo').value =
                    new Intl.NumberFormat('id-ID').format(saldo);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua tombol inisiasi saldo
            const initSaldoButtons = document.querySelectorAll('.btn-init-saldo');

            initSaldoButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Ambil data dari tombol
                    const idPetani = this.dataset.id_petani;
                    const idDesa = this.dataset.id_desa;
                    const idTahun = this.dataset.id_tahun_tanam;
                    const nama = this.dataset.nama;
                    const plasma = this.dataset.plasma;
                    const koperasi = this.dataset.koperasi;
                    const desa = this.dataset.desa;
                    const tahun = this.dataset.tahun;

                    // Masukkan ke modal
                    document.getElementById('m_id_petani').value = idPetani;
                    document.getElementById('m_id_desa').value = idDesa;
                    document.getElementById('m_id_tahun_tanam').value = idTahun;

                    document.getElementById('m_nama').textContent = nama;
                    document.getElementById('m_plasma').textContent = plasma;
                    document.getElementById('m_koperasi').textContent = koperasi;
                    document.getElementById('m_desa').textContent = desa;
                    document.getElementById('m_tahun').textContent = tahun;

                    // Reset saldo input
                    const saldoInput = document.getElementById('saldo');
                    saldoInput.value = '';
                    saldoInput.focus();
                });
            });

            // Format Rupiah
            const saldoInput = document.getElementById('saldo');
            saldoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = formatRupiah(value);
            });

            function formatRupiah(angka) {
                if (!angka) return '';
                return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.delete-form');

                    Swal.fire({
                        title: "<h3 style='font-size:15px;margin-bottom:2px;line-height:0.5;'>Yakin ingin menghapus?</h3>",
                        html: "<p style='font-size:14px;margin:0;'>Data yang dihapus tidak dapat dikembalikan!</p>",
                        icon: 'warning',
                        iconColor: '#dc3545',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#dc3545',
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    {{-- Notifikasi sukses --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: "<h3 style='font-size:15px;margin-bottom:0;'>Berhasil</h3>",
                text: "{{ session('success') }}",
                confirmButtonColor: '#198754',
                timer: 1800,
                showConfirmButton: false
            });
        </script>
    @endif

    {{-- Notifikasi error atau gagal --}}
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: "<h3 style='font-size:15px;margin-bottom:0;'>Gagal</h3>",
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc3545'
            });
        </script>
    @endif

    {{-- Notifikasi peringatan --}}
    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                iconColor: '#dc3545',
                title: "<h3 style='font-size:15px;margin-bottom:0;'>Perhatian</h3>",
                text: "{{ session('warning') }}",
                confirmButtonColor: '#f0ad4e'
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.auto-submit').forEach(input => {
                input.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });
    </script>
@endsection
