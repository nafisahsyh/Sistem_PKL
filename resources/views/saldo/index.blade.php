@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container-fluid px-4 mt-5">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-brown mb-0">Catatan Saldo</h3>
            <a href="{{ route('saldo.pdf', request()->all()) }}" target="_blank"
                class="btn btn-danger d-flex align-items-center gap-2">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
        </div>
        @php
            $filters = [
                'id_desa' => request('id_desa'),
                'id_tahun_tanam' => request('id_tahun_tanam'),
                'periode' => request('periode'),
                'tahun' => request('tahun'),
                'metode' => request('metode'),
            ];

            $jumlahFilterAktif = collect($filters)->filter(fn($v) => filled($v))->count();

            $totalPetani = $dataSaldo->count();
            $sudahMengambil = $dataSaldo->where('status_metode', '!=', 'Belum diambil')->count();
            $belumMengambil = $dataSaldo->where('status_metode', 'Belum diambil')->count();

            $cash = $dataSaldo->where('status_metode', 'cash');
            $transfer = $dataSaldo->where('status_metode', 'transfer');

            $jumlahCash = $cash->count();
            $nominalCash = $cash->sum('total_nominal');

            $jumlahTransfer = $transfer->count();
            $nominalTransfer = $transfer->sum('total_nominal');

            $totalNominalKeseluruhan = $dataSaldo->sum('total_nominal');

            $cards = [
                [
                    'title' => 'Total Nominal',
                    'count' => 'Rp ' . number_format($totalNominalKeseluruhan, 0, ',', '.'),
                    'bg' => 'bg-secondary',
                    'text' => 'text-white',
                ],
                [
                    'title' => 'Total Petani',
                    'count' => $totalPetani,
                    'bg' => 'bg-primary',
                    'text' => 'text-white',
                ],
                [
                    'title' => 'Sudah Mengambil',
                    'count' => $sudahMengambil,
                    'bg' => 'bg-success',
                    'text' => 'text-white',
                ],
                [
                    'title' => 'Belum Mengambil',
                    'count' => $belumMengambil,
                    'bg' => 'bg-danger',
                    'text' => 'text-white',
                ],
                [
                    'title' => 'Cash',
                    'count' => $jumlahCash . ' orang',
                    'extra' => 'Rp ' . number_format($nominalCash, 0, ',', '.'),
                    'bg' => 'bg-info',
                    'text' => 'text-dark',
                ],
                [
                    'title' => 'Transfer',
                    'count' => $jumlahTransfer . ' orang',
                    'extra' => 'Rp ' . number_format($nominalTransfer, 0, ',', '.'),
                    'bg' => 'bg-warning',
                    'text' => 'text-dark',
                ],
            ];
        @endphp

        @if ($jumlahFilterAktif > 0)
            <div class="d-flex flex-wrap gap-2 mb-3 justify-content-end">

                @foreach ($cards as $card)
                    <div class="summary-card {{ $card['bg'] }} {{ $card['text'] }}">
                        <small>{{ $card['title'] }}</small>
                        <strong>{{ $card['count'] }}</strong>

                        @isset($card['extra'])
                            <small>{{ $card['extra'] }}</small>
                        @endisset
                    </div>
                @endforeach
            </div>
        @endif
        
        {{-- CARD --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    {{-- BUTTON FILTER --}}
                    <button
                        class="btn d-flex align-items-center gap-2
                    {{ $jumlahFilterAktif > 0 ? 'btn-success text-white' : 'btn-outline-success' }}"
                        data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter"></i> Filter
                        @if ($jumlahFilterAktif > 0)
                            <span class="badge bg-warning text-dark">{{ $jumlahFilterAktif }}</span>
                        @endif
                    </button>

                    {{-- SEARCH --}}
                    <form action="{{ route('saldo.index') }}" method="GET"
                        class="d-flex align-items-start flex-wrap justify-content-end gap-2">

                        {{-- Keep ALL active filters --}}
                        @foreach (['id_desa', 'id_tahun_tanam', 'periode', 'tahun', 'metode'] as $f)
                            @if (request()->filled($f))
                                <input type="hidden" name="{{ $f }}" value="{{ request($f) }}">
                            @endif
                        @endforeach

                        <input type="text" name="search" class="form-control form-control-search"
                            placeholder="Cari Nama/No Plasma..." value="{{ request('search') }}" style="width: 250px;">

                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-search"></i>
                        </button>

                        {{-- Reset search only, but keep filters --}}
                        <a href="{{ route('saldo.index', [
                            'id_desa' => request('id_desa'),
                            'id_tahun_tanam' => request('id_tahun_tanam'),
                            'periode' => request('periode'),
                            'tahun' => request('tahun'),
                            'metode' => request('metode'),
                        ]) }}"
                            class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>


                <table class="table table-bordered table-striped align-middle table-custom">
                    <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                        <tr>
                            <th>No</th>
                            <th>No Plasma</th>
                            <th>Nama Petani</th>
                            <th>Desa</th>
                            <th>Tahun Tanam</th>
                            <th>Luasan Total (Ha)</th>
                            <th>Periode</th>
                            <th>Nominal</th>
                            <th>Sisa</th>
                            <th>Metode</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($dataSaldo as $i => $row)
                            <tr>
                                <td class="text-center">
                                    {{ $dataSaldo->firstItem() + $i }}
                                </td>

                                <td class="text-center">{{ $row->nomor_plasma }}</td>

                                <td>{{ $row->nama_petani }}</td>

                                <td class="text-center">{{ $row->nama_desa }}</td>

                                <td class="text-center">{{ $row->tahun_tanam }}</td>

                                <td class="text-center">
                                    {{ number_format($row->luasan, 2, ',', '.') }}
                                </td>

                                <td class="text-center">{{ $row->periode }}</td>

                                <td class="text-end">
                                    Rp {{ number_format($row->total_nominal, 0, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    Rp {{ number_format($row->sisa, 0, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    @if ($row->status_metode == 'Belum diambil')
                                        <span class="badge bg-danger">Belum</span>
                                    @elseif($row->status_metode == 'cash')
                                        <span class="badge bg-primary">Cash</span>
                                    @else
                                        <span class="badge bg-success">Transfer</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                    Belum ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $dataSaldo->links('vendor.pagination.grouped') }}
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FILTER -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">

                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-filter me-2"></i> Filter Catatan Saldo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('saldo.index') }}" method="GET">
                    <div class="modal-body px-4">
                        <div class="row g-2">

                            {{-- Desa --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Filter Desa</label>
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Filter Tahun Tanam</label>
                                <select name="id_tahun_tanam" id="filter_tahun_tanam" class="form-select">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($tahunTanam as $t)
                                        <option value="{{ $t->id_tahun_tanam }}"
                                            {{ request('id_tahun_tanam') == $t->id_tahun_tanam ? 'selected' : '' }}>
                                            {{ $t->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Periode --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Periode (2 Bulanan)</label>
                                <select name="periode" id="filter_periode" class="form-select">
                                    <option value="">Semua Periode</option>
                                    <option value="1" {{ request('periode') == 1 ? 'selected' : '' }}>Januari-Februari
                                    </option>
                                    <option value="2" {{ request('periode') == 2 ? 'selected' : '' }}>Maret-April
                                    </option>
                                    <option value="3" {{ request('periode') == 3 ? 'selected' : '' }}>Mei-Juni
                                    </option>
                                    <option value="4" {{ request('periode') == 4 ? 'selected' : '' }}>Juli-Agustus
                                    </option>
                                    <option value="5" {{ request('periode') == 5 ? 'selected' : '' }}>
                                        September-Oktober</option>
                                    <option value="6" {{ request('periode') == 6 ? 'selected' : '' }}>
                                        November-Desember</option>
                                </select>
                            </div>

                            {{-- Tahun --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tahun</label>
                                <input type="number" name="tahun" class="form-control" placeholder="Misal: 2025"
                                    value="{{ request('tahun') }}" min="2000" max="3000">
                            </div>

                            {{-- Metode Pengambilan --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Metode Pengambilan</label>
                                <select name="metode" id="filter_metode" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="cash" {{ request('metode') == 'cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="transfer" {{ request('metode') == 'transfer' ? 'selected' : '' }}>
                                        Transfer</option>
                                    <option value="belum" {{ request('metode') == 'belum' ? 'selected' : '' }}>Belum
                                        Diambil</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('saldo.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Terapkan Filter
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
            const selects = ['filter_desa', 'filter_tahun_tanam', 'filter_periode', 'filter_metode'];
            selects.forEach(id => {
                const el = document.getElementById(id);
                if (el) new Choices(el, {
                    shouldSort: false,
                    searchPlaceholderValue: "Cari...",
                    itemSelectText: '',
                });
            });
        });
    </script>
@endsection
