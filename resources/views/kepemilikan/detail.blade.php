@extends('theme.default')

@section('content')
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- Tombol Aksi --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('kepemilikan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
            <h3 class="text-brown mb-0">Detail Kepemilikan</h3>
            <a href="{{ route('kepemilikan.pdf', $kepemilikan->id_kepemilikan) }}" class="btn btn-danger shadow-sm" target="_blank">
                <i class="fa fa-file-pdf"></i> Cetak PDF
            </a>
            <a href="{{ route('kepemilikan.edit', $kepemilikan->id_kepemilikan) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>

        {{-- Data Petani --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Data Petani</strong>
            </div>
            <div class="card-body">
                <p><strong>Nama:</strong> {{ $kepemilikan->petani->nama ?? '-' }}</p>
                <p><strong>NIK:</strong> {{ $kepemilikan->petani->NIK ?? '-' }}</p>
                <p><strong>Nomor Anggota Koperasi:</strong> {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</p>
                <p><strong>Nomor Anggota Plasma:</strong> {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $kepemilikan->petani->alamat ?? '-' }}</p>
                @if(!empty($kepemilikan->petani->pdf_scan_ktp))
                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}" target="_blank"
                        class="btn btn-info btn-sm text-white">
                        <i class="fas fa-file-pdf"></i> Lihat Dokumen KTP
                    </a>
                @endif
            </div>
        </div>

        {{-- Data Kepemilikan + Lahan --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <strong>Data Kepemilikan & Lahan</strong>
            </div>
            <div class="card-body">
                @if($kepemilikan->detailKepemilikan->count() > 0)
                    @foreach($kepemilikan->detailKepemilikan as $index => $detail)
                        <div class="border p-3 rounded mb-4 bg-light">
                            <h5 class="text-success">Lahan {{ $index + 1 }}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="25%">Desa</th>
                                    <td>{{ $detail->lahan->desa->desa ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Kecamatan</th>
                                    <td>{{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tahun Tanam</th>
                                    <td>{{ $detail->lahan->tahunTanam->tahun ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Luas Peta</th>
                                    <td>{{ number_format($detail->lahan->luas_peta, 2, ',', '.') }} m²</td>
                                </tr>
                                <tr>
                                    <th>Nomor SHM</th>
                                    <td>{{ $detail->nomor_SHM ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Nomor Sporadik</th>
                                    <td>{{ $detail->nomor_sporadik ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Nomor PBB</th>
                                    <td>{{ $detail->nomor_pbb ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah PBB</th>
                                    <td>Rp {{ number_format($detail->jumlah_pbb, 2, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Status Kepemilikan</th>
                                    <td>
                                        <span
                                            class="badge {{ $detail->status_kepemilikan == 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($detail->status_kepemilikan) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Mulai</th>
                                    <td>{{ \Carbon\Carbon::parse($detail->tanggal_mulai)->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Selesai</th>
                                    <td>{{ $detail->tanggal_selesai ? \Carbon\Carbon::parse($detail->tanggal_selesai)->format('d-m-Y') : '-' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Belum ada detail lahan untuk kepemilikan ini.</p>
                @endif
            </div>
        </div>
    </div>
@endsection