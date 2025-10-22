@extends('theme.default')

@section('content')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

<div class="container mt-5">
    <h3 class="mb-4 text-brown">Detail Kepemilikan</h3>

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
                <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}" target="_blank" class="btn btn-info btn-sm text-white">
                    <i class="fas fa-file-pdf"></i> Lihat Dokumen KTP
                </a>
            @endif
        </div>
    </div>

    {{-- Data Kepemilikan --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>Data Kepemilikan</strong>
        </div>
        <div class="card-body">
            <p><strong>Nomor SHM:</strong> {{ $kepemilikan->nomor_SHM ?? '-' }}</p>
            <p><strong>Nomor Sporadik:</strong> {{ $kepemilikan->nomor_sporadik ?? '-' }}</p>
            <p><strong>Luas Surat:</strong> {{ number_format($kepemilikan->luas_surat, 2, ',', '.') }} m²</p>
            <p><strong>Nomor PBB:</strong> {{ $kepemilikan->nomor_pbb ?? '-' }}</p>
            <p><strong>Jumlah PBB:</strong> Rp {{ number_format($kepemilikan->jumlah_pbb, 2, ',', '.') }}</p>
            <p><strong>Status:</strong> 
                <span class="badge {{ $kepemilikan->status_kepemilikan == 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                    {{ ucfirst($kepemilikan->status_kepemilikan) }}
                </span>
            </p>
            <p><strong>Tanggal Mulai:</strong> {{ \Carbon\Carbon::parse($kepemilikan->tanggal_mulai)->format('d-m-Y') }}</p>
            <p><strong>Tanggal Selesai:</strong> {{ $kepemilikan->tanggal_selesai ? \Carbon\Carbon::parse($kepemilikan->tanggal_selesai)->format('d-m-Y') : '-' }}</p>
        </div>
    </div>

    {{-- Detail Lahan --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-warning text-dark">
            <strong>Detail Lahan yang Dimiliki</strong>
        </div>
        <div class="card-body">
            @if($kepemilikan->detailKepemilikan->count() > 0)
                <table class="table table-bordered table-striped">
                    <thead class="text-center bg-light">
                        <tr>
                            <th>No</th>
                            <th>Desa</th>
                            <th>Kecamatan</th>
                            <th>Tahun Tanam</th>
                            <th>Luas Peta (m²)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kepemilikan->detailKepemilikan as $index => $detail)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $detail->lahan->desa->desa ?? '-' }}</td>
                                <td>{{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}</td>
                                <td>{{ $detail->lahan->tahunTanam->tahun ?? '-' }}</td>
                                <td class="text-end">{{ number_format($detail->lahan->luas_peta, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Belum ada detail lahan untuk kepemilikan ini.</p>
            @endif
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="d-flex justify-content-between">
        <a href="{{ route('kepemilikan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('kepemilikan.edit', $kepemilikan->id_kepemilikan) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>
@endsection
