@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            <a href="{{ route('pengambilan.index', [
                'id_desa' => request('id_desa'),
                'id_tahun_tanam' => request('id_tahun_tanam'),
                'periode' => request('periode'),
                'tahun' => request('tahun'),
            ]) }}"
                class="btn btn-success p-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>
            <h4 class="text-brown mb-0">Detail Pengambilan Saldo</h4>
        </div>

        {{-- Informasi Desa & Periode --}}
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Informasi Periode</h5>
                @php
                    $bulanIndonesia = [
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ];
                @endphp
                <table class="table table-bordered table-custom align-middle mb-3 text-center">
                    <thead style="background-color: #cce1d7; color: #014C2D;">
                        <tr>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Periode</th>
                            <th>Total Nominal</th>
                            <th>Saldo Periode Lalu</th>
                            <th>Total Saldo</th>
                        </tr>

                    </thead>
                    <tbody>
                        <tr style="background-color: #ffffff; color: #014C2D;">
                            <td>{{ $desa->desa }}</td>
                            <td>{{ $tahunTanam->tahun }}</td>
                            <td>
                                {{ $bulanIndonesia[$bulan_awal] ?? $bulan_awal }}
                                @if ($bulan_awal != $bulan_akhir)
                                    - {{ $bulanIndonesia[$bulan_akhir] ?? $bulan_akhir }}
                                @endif
                                {{ $tahun }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($total_periode, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($saldo_periode_lalu, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($total_saldo_periode, 2, ',', '.') }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daftar Petani --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Petani</h5>

                    {{-- Search Form --}}
                    <form action="{{ route('pengambilan.show') }}" method="GET" class="d-flex align-items-center">
                        @foreach (request('id_bulanan') as $id)
                            <input type="hidden" name="id_bulanan[]" value="{{ $id }}">
                        @endforeach
                        <input type="text" name="search" class="form-control me-2"
                            placeholder="Cari Nomor atau Nama Petani..." value="{{ request('search') }}"
                            style="width: 300px; height: 38px; font-size: 0.9rem;">
                        <button class="btn btn-success d-flex align-items-center justify-content-center" type="submit"
                            style="height: 38px; width: 38px;" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('pengambilan.show', ['id_bulanan' => request('id_bulanan')]) }}"
                            class="btn btn-primary ms-2 d-flex align-items-center justify-content-center"
                            style="height: 38px; width: 38px;" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <table class="table table-bordered table-striped table-custom align-middle">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>No Koperasi</th>
                            <th>Nama Petani</th>
                            <th>Luas Lahan</th>
                            <th>Nominal</th>
                            <th>Saldo Periode Lalu</th> {{-- kolom baru --}}
                            <th>Total Hak</th> {{-- kolom baru --}}
                            <th>Aksi Ambil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($petaniData as $i => $p)
                            <tr>
                                <td class="text-center">{{ $noStart + $i }}</td>
                                <td class="text-center">{{ $p['no_plasma'] ?? '-' }}</td>
                                <td class="text-center">{{ $p['no_koperasi'] ?? '-' }}</td>
                                <td>{{ $p['nama_petani'] }}</td>
                                <td class="text-center">{{ number_format($p['luas_ha'], 2, ',', '.') }} Ha</td>
                                <td class="text-end">Rp {{ number_format($p['nominal'], 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($p['saldo_periode_lalu'], 2, ',', '.') }}</td>
                                {{-- saldo periode lalu --}}
                                <td class="text-end">Rp {{ number_format($p['total_hak'], 2, ',', '.') }}</td>
                                {{-- total hak --}}
                                <td class="text-center">
                                    {{-- Aksi Ambil / Status --}}
                                    @if ($p['sudah_diambil'])
                                        <span class="badge bg-secondary">Sudah diambil</span>
                                    @elseif ($p['periode_berlanjut'])
                                        <span class="badge bg-warning text-dark">Periode Berlanjut</span>
                                    @elseif ($p['total_hak'] > 0)
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalAmbil_{{ $p['id_petani'] }}">
                                            <i class="fa fa-file-invoice-dollar"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif

                                    {{-- Print Nota (TIDAK BOLEH KETUTUP) --}}
                                    @if ($p['sudah_diambil'] && !empty($p['trx_periode']))
                                        <a href="{{ route('ambil-saldo.struk', $p['trx_periode']->id_transaksi) }}"
                                            target="_blank" class="btn btn-primary btn-sm ms-1">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                                    <div>Belum ada data petani</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $petaniData->links('vendor.pagination.grouped') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ambil untuk setiap petani --}}
    @foreach ($petaniData as $p)
        <div class="modal fade" id="modalAmbil_{{ $p['id_petani'] }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Ambil Saldo Petani</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('ambil-saldo.store') }}" method="POST" target="_blank" class="formAmbil">
                        @csrf

                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            @php
                                $bulanSingkat = [
                                    1 => 'Jan',
                                    2 => 'Feb',
                                    3 => 'Mar',
                                    4 => 'Apr',
                                    5 => 'Mei',
                                    6 => 'Jun',
                                    7 => 'Jul',
                                    8 => 'Agt',
                                    9 => 'Sep',
                                    10 => 'Okt',
                                    11 => 'Nov',
                                    12 => 'Des',
                                ];
                                $nextToday =
                                    \App\Models\Transaksi::where('tipe', 'debit_pengambilan')
                                        ->whereDate('tanggal', now()->toDateString())
                                        ->count() + 1;
                            @endphp

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">No Tanda Terima<span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="no_urut" class="form-control" min=0
                                        placeholder="Isi nomor urut" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Metode<span class="text-danger">*</span></label>
                                    <select name="metode" class="form-select text-kecil choices-select" required>
                                        <option value="">Pilih Metode</option>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Tanggal Bukti</label>
                                    <input type="date" name="tanggal" class="form-control tanggalTransaksi"
                                        value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">No Bukti</label>
                                    <input type="text" name="no_bukti" class="form-control noBukti"
                                        data-next="{{ $nextToday }}"
                                        value="{{ $nextToday . '.' . now()->format('d/m/Y') }}" readonly>

                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <label class="form-label fw-bold">Nama Petani</label>
                                    <input type="text" class="form-control" value="{{ $p['nama_petani'] }}" readonly>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-bold">No Plasma</label>
                                    <input type="text" class="form-control" value="{{ $p['no_plasma'] ?? '-' }}"
                                        readonly>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-bold">No Koperasi</label>
                                    <input type="text" class="form-control" value="{{ $p['no_koperasi'] }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <label class="form-label fw-bold">Desa</label>
                                    <input type="text" class="form-control" value="{{ $desa->desa }}" readonly>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-bold">Tahun Tanam</label>
                                    <input type="text" class="form-control" value="{{ $tahunTanam->tahun }}"
                                        readonly>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-bold">Luas Lahan (Ha)</label>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($p['luas_ha'], 2, ',', '.') }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                @if (!$p['pakai_mode_periode'])
                                    <div class="col-4">
                                        <label class="form-label fw-bold">
                                            Saldo Bulan {{ $bulanSingkat[$bulan_awal] }}
                                        </label>
                                        <input type="text" class="form-control"
                                            value="Rp {{ number_format($p['nominal_bulan_1'], 2, ',', '.') }}" readonly>
                                    </div>

                                    <div class="col-4">
                                        <label class="form-label fw-bold">
                                            Saldo Bulan {{ $bulanSingkat[$bulan_akhir] }}
                                        </label>
                                        <input type="text" class="form-control"
                                            value="Rp {{ number_format($p['nominal_bulan_2'], 2, ',', '.') }}" readonly>
                                    </div>

                                    <div class="col-4">
                                        <label class="form-label fw-bold">Total Saldo</label>
                                        <input type="text" class="form-control"
                                            value="Rp {{ number_format($p['nominal'], 2, ',', '.') }}" readonly>
                                    </div>
                                @else
                                    @foreach ($p['saldo_periode_list'] as $sp)
                                        @php
                                            $awal = \Carbon\Carbon::parse($sp['bulan_awal']);
                                            $akhir = \Carbon\Carbon::parse($sp['bulan_akhir']);
                                        @endphp
                                        <div class="col-4 mb-3">
                                            <label class="form-label fw-bold">
                                                {{ $bulanSingkat[$awal->month] }} {{ $awal->year }}
                                                -
                                                {{ $bulanSingkat[$akhir->month] }} {{ $akhir->year }}

                                            </label>
                                            <input type="text" class="form-control"
                                                value="Rp {{ number_format($sp['saldo'], 2, ',', '.') }}" readonly>
                                        </div>
                                    @endforeach

                                    <div class="col-4 mb-3">
                                        <label class="form-label fw-bold">Total Saldo</label>
                                        <input type="text" class="form-control"
                                            value="Rp {{ number_format($p['total_saldo_akumulasi'], 2, ',', '.') }}"
                                            readonly>
                                    </div>
                                @endif
                            </div>

                            <input type="hidden" name="id_petani" value="{{ $p['id_petani'] }}">
                            @foreach ($id_bulanan as $id)
                                <input type="hidden" name="id_bulanan[]" value="{{ $id }}">
                            @endforeach
                            <input type="hidden" name="bulan_awal"
                                value="{{ $tahun . '-' . str_pad($bulan_awal, 2, '0', STR_PAD_LEFT) }}">
                            <input type="hidden" name="bulan_akhir"
                                value="{{ $tahun . '-' . str_pad($bulan_akhir, 2, '0', STR_PAD_LEFT) }}">

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary btnSubmitSaldo">
                                Ambil Saldo
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.querySelectorAll('.choices-select').forEach(el => {
            new Choices(el, {
                searchEnabled: false,
                shouldSort: false,
                itemSelectText: '',
                placeholderValue: 'Pilih Metode',
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btnSubmitSaldo').forEach(btn => {
            btn.addEventListener('click', function() {
                let form = this.closest('form');
                let no_urut = form.querySelector('input[name="no_urut"]').value;
                let metode = form.querySelector('select[name="metode"]').value;

                if (no_urut === '' || metode === '') {
                    Swal.fire({
                        title: "Data belum lengkap",
                        text: "Nomor urut dan metode harus diisi.",
                        icon: "warning",
                        iconColor: '#dc3545',
                        confirmButtonColor: '#198754',
                    });
                    return;
                }

                Swal.fire({
                    title: "Ambil saldo petani?",
                    text: "Saldo akan menjadi 0 dan nota akan diterbitkan.",
                    icon: "warning",
                    iconColor: '#dc3545',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Proses',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // langsung submit → buka tab nota
                    }
                });
            });
        });

        document.addEventListener("visibilitychange", function() {
            // Jika user kembali ke tab ini
            if (document.visibilityState === "visible") {
                // Reload otomatis
                window.location.reload();
            }
        });
    </script>

    <script>
        document.addEventListener('input', function(e) {

            if (!e.target.classList.contains('tanggalTransaksi')) return;

            const modal = e.target.closest('.modal');
            const noBuktiInput = modal.querySelector('.noBukti');

            const urut = noBuktiInput.dataset.next;
            const tanggal = e.target.value; // yyyy-mm-dd

            if (!tanggal) return;

            const d = new Date(tanggal);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();

            noBuktiInput.value = `${urut}.${day}/${month}/${year}`;
        });
    </script>
@endsection
