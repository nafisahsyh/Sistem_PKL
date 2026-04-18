@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
    <div class="container-fluid px-4 mt-5">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            {{-- Tombol Kembali --}}
            <a href="{{ route('kepemilikan.index', [
                'page' => request('page'),
                'search' => request('search'),
                'desa' => request('desa'),
                'tahun' => request('tahun'),
                'status_pengelolaan' => request('status_pengelolaan'),
                'status_petani' => request('status_petani'),
            ]) }}"
                class="btn btn-success p-2" title="Kembali ke Data Kepemilikan">
                <i class="fas fa-chevron-left fa-lg"></i>
            </a>

            {{-- Judul --}}
            <h4 class="text-brown mb-0">Detail Kepemilikan Per Lahan</h4>

            {{-- Tombol aksi --}}
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('kepemilikan.cetakPerLahan', ['id_kepemilikan' => $kepemilikan->id_kepemilikan, 'id_detail_kepemilikan' => $selectedDetail->id_detail_kepemilikan]) }}"
                    class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf"></i> Cetak PDF
                </a>
                <a href="{{ route('kepemilikan.editPerLahan', ['id_kepemilikan' => $kepemilikan->id_kepemilikan, 'id_lahan' => $selectedDetail->id_lahan]) }}"
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
                            <th class="text-normal text-start ps-3">Telepon</th>
                            <td class="text-normal-sm text-start ps-3" text-start ps-3>
                                {{ $kepemilikan->petani->no_telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Plasma</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Nomor Koperasi</th>
                            <td class="text-normal-sm text-start ps-3">
                                {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Status Petani</th>
                            <td class="text-normal-sm text-start ps-3">
                                <span
                                    class="badge {{ $kepemilikan->petani->status == 'aktif'
                                        ? 'bg-success'
                                        : ($kepemilikan->petani->status == 'tidak_aktif'
                                            ? 'bg-secondary'
                                            : ($kepemilikan->petani->status == 'berhenti'
                                                ? 'bg-danger'
                                                : 'bg-secondary')) }}">
                                    {{ ucfirst($kepemilikan->petani->status) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th class="text-normal text-start ps-3">Alamat</th>
                            <td class="text-normal-sm text-start ps-3">{{ $kepemilikan->petani->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-normal text-start ps-3">Scan KTP</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($kepemilikan->petani->pdf_scan_ktp))
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_ktp) }}"
                                        target="_blank" class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada file</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th class="text-normal text-start ps-3">Scan KK</th>
                            <td class="text-normal-sm text-start ps-3">
                                @if (!empty($kepemilikan->petani->pdf_scan_kk))
                                    <a href="{{ asset('storage/ktp_pdf/' . $kepemilikan->petani->pdf_scan_kk) }}"
                                        target="_blank" class="btn btn-info btn-sm text-dark">
                                        <i class="fas fa-file-pdf"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada file</span>
                                @endif
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB LAHAN --}}
        @if ($groupDetails->count() > 1)
            <ul class="nav flex-nowrap gap-2 custom-tabs mb-3">
                @foreach ($groupDetails as $index => $detail)
                    <li class="nav-item">
                        <button class="nav-link {{ $index == 0 ? 'active' : '' }}" data-bs-toggle="tab"
                            data-bs-target="#lahan{{ $index }}">

                            {{ $index + 1 }}
                            {{ $detail->lahan->desa->desa ?? '-' }}
                            {{ $detail->lahan->tahunTanam->tahun ?? '-' }}

                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
        {{-- Data Lahan --}}
        <div class="tab-content">

            @foreach ($groupDetails as $index => $detail)
                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="lahan{{ $index }}">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <strong>Data Lahan {{ $loop->iteration }}</strong>
                            <a href="{{ route('kepemilikan.riwayatLahan', [
                                'id_lahan' => $detail->id_lahan,
                                'page' => request('page'),
                                'search' => request('search'),
                                'desa' => request('desa'),
                                'tahun' => request('tahun'),
                                'status_pengelolaan' => request('status_pengelolaan'),
                                'status' => request('status'),
                            ]) }}"
                                class="btn btn-info btn-sm text-dark">
                                <i class="fas fa-history"></i> Riwayat
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Status Kelola</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            <span
                                                class="badge
        @if ($detail->status_pengelolaan == 'KSM') bg-success
        @elseif ($detail->status_pengelolaan == 'Mandiri') bg-primary
        @elseif ($detail->status_pengelolaan == 'Perusahaan') bg-warning text-dark
        @else bg-secondary @endif">

                                                {{ $detail->status_pengelolaan == 'Perusahaan' ? 'HSU-SSA' : $detail->status_pengelolaan ?? '-' }}

                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3" width="30%">Desa</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->desa->desa ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Kecamatan</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Tahun Tanam</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ $detail->lahan->tahunTanam->tahun ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Kode Lahan</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->kode_lahan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Nomor Kavling</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_kavling ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Luas Sesuai Lapangan</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ number_format($detail->lahan->luas_peta ?? 0, 0, ',', '.') }} M²
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Luas Sesuai Surat</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            {{ number_format($detail->luas_surat ?? 0, 0, ',', '.') }} M²
                                        </td>
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
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_sporadik ?? '-' }}
                                        </td>
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
                                        <td class="text-normal-sm text-start ps-3">
                                            Rp {{ number_format($detail->jumlah_pbb ?? 0, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Koordinat Lahan</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            @php
                                                $x = $detail->koordinat_x;
                                                $y = $detail->koordinat_y;

                                                echo $x && $y ? $x . ', ' . $y : '- , -';
                                            @endphp
                                        </td>
                                    </tr>

                                    {{-- Riwayat Pembayaran PBB --}}
                                    <tr>
                                        <th class="text-normal text-start ps-3">Riwayat Pembayaran PBB</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            @php
                                                $pbbList = $detail->pbb->sortByDesc('tahun');
                                            @endphp

                                            @if ($pbbList->isNotEmpty())
                                                @foreach ($pbbList as $p)
                                                    <div class="mb-1">
                                                        <strong>{{ $p->tahun }}</strong> :
                                                        Rp {{ number_format($p->jumlah, 2, ',', '.') }}

                                                        <span
                                                            class="badge {{ $p->status == 'lunas' ? 'bg-success' : 'bg-danger' }}">
                                                            {{ ucfirst($p->status) }}
                                                        </span>

                                                        @if ($p->status == 'belum')
                                                            <form action="{{ route('pbb.lunas', $p->id_pbb) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-success ms-2">
                                                                    <i class="fas fa-check"></i> Lunas
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Belum ada data PBB tahunan.</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="text-normal text-start ps-3">Posisi Surat</th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->posisi_surat ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Status Penyerahan Surat </th>
                                        <td class="text-normal-sm text-start ps-3">{{ $detail->status_penyerahan ?? '-' }}
                                        </td>
                                    </tr>

                                    {{-- File SHM --}}
                                    <tr>
                                        <th class="text-normal text-start ps-3">File SHM</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            @if (!empty($detail->pdf_scan_shm))
                                                <a href="{{ asset('storage/' . $detail->pdf_scan_shm) }}" target="_blank"
                                                    class="btn btn-info btn-sm text-dark">
                                                    <i class="fas fa-file-pdf"></i> Lihat
                                                </a>

                                                <a href="{{ route('shm.download', $detail->id_detail_kepemilikan) }}"
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-download"></i> Unduh
                                                </a>
                                            @else
                                                <span class="text-muted">Tidak ada file</span>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- File Peta --}}
                                    <tr>
                                        <th class="text-normal text-start ps-3">Peta Lahan</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            @if (!empty($detail->pdf_scan_peta))
                                                <a href="{{ asset('storage/' . $detail->pdf_scan_peta) }}"
                                                    target="_blank" class="btn btn-info btn-sm text-dark">
                                                    <i class="fas fa-file-pdf"></i> Lihat
                                                </a>
                                            @else
                                                <span class="text-muted">Tidak ada file</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Status Lahan</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            @php
                                                $status = strtolower(trim($detail->status_kepemilikan));
                                            @endphp

                                            <span class="badge {{ $status === 'aktif' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-normal text-start ps-3">Tanggal Mulai</th>
                                        <td class="text-normal-sm text-start ps-3">
                                            @if (!empty($detail->tanggal_mulai))
                                                {{ \Carbon\Carbon::parse($detail->tanggal_mulai)->format('d-m-Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
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
            @endforeach
        </div>

        <script>
            window.addEventListener("load", function() {
                if (window.location.hash) {
                    const tab = document.querySelector('[data-bs-target="' + window.location.hash + '"]');
                    if (tab) new bootstrap.Tab(tab).show();
                }
            });
        </script>
    @endsection
