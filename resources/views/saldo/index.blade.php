@extends('theme.default')

@section('content')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

<div class="container-fluid px-4 mt-5">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h3 class="text-brown mb-0">Catatan Saldo</h3>
    </div>

    {{-- CARD --}}
    <div class="card shadow-sm rounded-3">
        <div class="card-body">

            <table class="table table-bordered table-striped align-middle table-custom">
                <thead class="text-center" style="background-color:#cce1d7; color:#014C2D;">
                    <tr>
                        <th>No</th>
                        <th>Nama Petani</th>
                        <th>No Plasma</th>
                        <th>Desa</th>
                        <th>Tahun Tanam</th>
                        <th>Luasan (Ha)</th>
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

                            <td>{{ $row->nama_petani }}</td>

                            <td class="text-center">{{ $row->nomor_plasma }}</td>

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
                                @if($row->status_metode == "Belum diambil")
                                    <span class="badge bg-danger">Belum</span>
                                @elseif($row->status_metode == "cash")
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
@endsection
