@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            {{-- Tombol Kembali sebagai icon saja --}}
            <a href="{{ route('kepemilikan.index') }}" class="btn btn-success p-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            {{-- Judul --}}
            <h3 class="text-brown mb-0">Detail Kepemilikan</h3>

            {{-- Tombol aksi di kanan --}}
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('kepemilikan.pdf', $kepemilikan->id_kepemilikan) }}" class="btn btn-danger shadow-sm"
                    target="_blank">
                    <i class="fa fa-file-pdf"></i> Cetak PDF
                </a>
                <a href="{{ route('kepemilikan.edit', $kepemilikan->id_kepemilikan) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>


        {{-- Data Petani --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Data Petani</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 ">
                    <tbody>
                        <tr>
                            <th class="text-normal text-start ps-3" width="30%">Nama</th>
                            <td class="text-normal-sm text-start ps-3" text-start ps-3>
                                {{ $kepemilikan->petani->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">NIK</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->NIK ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Anggota Koperasi</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Anggota Plasma</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Status Kepemilikan</th>
                            <td class="text-normal-sm text-start ps-3">
                                <span
                                    class="badge {{ $kepemilikan->status_kepemilikan == 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($kepemilikan->status_kepemilikan) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Alamat</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Dokumen</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($kepemilikan->petani->pdf_scan_ktp))
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}"
                                        target="_blank" class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Data Kepemilikan & Lahan --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Data Kepemilikan & Lahan</strong>
            </div>
            <div class="card-body p-0">
                @forelse ($kepemilikan->detailKepemilikan as $index => $detail)
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light text-success">
                            <strong>Lahan {{ $index + 1 }}</strong>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th class="text-normal text-start ps-3" width="30%">Desa</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->desa->desa ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Kecamatan</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Tahun Tanam</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ $detail->lahan->tahunTanam->tahun ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Luas Peta</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ number_format($detail->lahan->luas_peta, 2, ',', '.') }} m²</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Nomor SHM</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_SHM ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Nomor Sporadik</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_sporadik ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Nomor PBB</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_pbb ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Jumlah PBB</th>
                                        <td class="text-normal-sm text-start ps-3">Rp
                                            {{ number_format($detail->jumlah_pbb, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Tanggal Mulai</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ \Carbon\Carbon::parse($detail->tanggal_mulai)->format('d-m-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Tanggal Selesai</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ $detail->tanggal_selesai ? \Carbon\Carbon::parse($detail->tanggal_selesai)->format('d-m-Y') : '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-muted p-3 mb-0">Belum ada detail lahan untuk kepemilikan ini.</p>
                @endforelse
            </div>
        </div>
    @endsection
