@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            {{-- Tombol Kembali --}}
            <a href="{{ route('kepemilikan.index', $kepemilikan->id_kepemilikan) }}" class="btn btn-success p-2">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            {{-- Judul --}}
            <h3 class="text-brown mb-0">Detail Kepemilikan Per Lahan</h3>

            {{-- Tombol aksi --}}
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('kepemilikan.cetakPerLahan', ['id_kepemilikan' => $kepemilikan->id_kepemilikan, 'id_detail_kepemilikan' => $detail->id_detail_kepemilikan]) }}"
                    class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf"></i> Cetak PDF
                </a>
                <a href="{{ route('kepemilikan.editPerLahan', ['id_kepemilikan' => $kepemilikan->id_kepemilikan, 'id_lahan' => $detail->id_lahan]) }}"
                    class="btn btn-warning">
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
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="text-normal text-start ps-3" width="30%">Nama</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->nama ?? '-' }}</td>
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
                                    class="badge {{ $detail->status_kepemilikan == 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($detail->status_kepemilikan) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th class="text-normal text-start ps-3">Alamat</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->alamat ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Data Lahan --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <strong>Data Lahan</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="text-normal text-start ps-3" width="30%">Desa</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->desa->desa ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Kecamatan</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Tahun Tanam</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->tahunTanam->tahun ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Luas Lahan Berdasarkan Peta</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ number_format($detail->lahan->luas_peta ?? 0, 2, ',', '.') }} m²</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Kavling</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_kavling ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Luas Lahan Berdasarkan Surat</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ number_format($detail->luas_surat ?? 0, 2, ',', '.') }} m²</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor SHM</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_SHM ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nama Sesuai SHM</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->nama_SHM ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Sporadik</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_sporadik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nama Sesuai Sporadik</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->nama_sporadik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor PBB</th>
                            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_pbb ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Jumlah PBB</th>
                            <td class="text-normal-sm text-start ps-3">Rp
                                {{ number_format($detail->jumlah_pbb ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">File SHM</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($detail->pdf_scan_shm))
                                    <a href="{{ asset('storage/' . $detail->pdf_scan_shm) }}" target="_blank"
                                        class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada file</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Peta Lahan</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($detail->pdf_scan_peta))
                                    <a href="{{ asset('storage/peta_pdf/' . $detail->pdf_scan_peta) }}" target="_blank"
                                        class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada file</span>
                                @endif
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
    </div>
@endsection
