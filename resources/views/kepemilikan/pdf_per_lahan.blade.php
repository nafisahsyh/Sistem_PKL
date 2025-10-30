<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Lahan Plasma</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 25px;
        }

        h2,
        h3,
        h4 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .header td {
            border: none;
            vertical-align: middle;
        }

        .header img {
            width: 80px;
            height: auto;
        }

        .kop-line {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: none;
            font-weight: bold;
        }

        .no-border th,
        .no-border td {
            border: none;
        }

        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-top: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .signature {
            width: 100%;
            margin-top: 40px;
            text-align: right;
        }

        .signature p {
            margin-bottom: 60px;
        }
    </style>
</head>

<body>

    <!-- KOP SURAT -->
    <div class="header">
        <table>
            <tr>
                <td width="15%" style="text-align: center; padding-right: 0px; padding-left: 15px">
                    <img src="{{ public_path('logo.png') }}" alt="Logo" style="width: 95px; height: auto;">
                </td>
                <td style="text-align: center;">
                    <h2 style="font-size: 18px;"><strong>KOPERASI SAWIT MAKMUR</strong></h2>
                    <h3 style="font-size: 15px;">KABUPATEN TANAH LAUT</h3>
                    <h4 style="font-size: 14px;">KALIMANTAN SELATAN</h4>
                    <p style="font-size: 11px; margin: 2px 0 0 0;">
                        Alamat: Jl. A. Yani Kel. Sarang Halang RT. 04 Kec. Pelaihari
                    </p>
                    <p style="font-size: 11px; margin: 0;">
                        Email: <strong>kop.sm.13@gmail.com</strong>
                    </p>
                </td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>

    <!-- JUDUL -->
    <h2 style="text-align: center; margin-top: 10px; margin-bottom: 15px;">
        <strong>DATA KEPEMILIKAN LAHAN PLASMA</strong>
    </h2>

    {{-- Data Petani --}}
    <div class="section-title">Data Petani</div>
    <table class="no-border">
        <tr>
            <th width="30%">Nama</th>
            <td>: {{ $kepemilikan->petani->nama ?? '-' }}</td>
        </tr>
        <tr>
            <th>NIK</th>
            <td>: {{ $kepemilikan->petani->NIK ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nomor Anggota Koperasi</th>
            <td>: {{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nomor Anggota Plasma</th>
            <td>: {{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</td>
        </tr>
        <tr>
            <th>Status Petani</th>
            <td>: {{ ucfirst($kepemilikan->petani->status) }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>: {{ $kepemilikan->petani->alamat ?? '-' }}</td>
        </tr>
    </table>

    {{-- Data Lahan --}}
    <div class="section-title">Data Lahan</div>
    <table>
        <tr>
            <th width="35%">Desa</th>
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
            <th>Luas Lahan Berdasarkan Peta</th>
            <td>{{ number_format($detail->lahan->luas_peta, 2, ',', '.') }} m²</td>
        </tr>
        <tr>
            <th>Luas Lahan Berdasarkan Surat</th>
            <td>{{ number_format($detail->luas_surat, 2, ',', '.') }} m²</td>
        </tr>
        <tr>
            <th>Nomor SHM</th>
            <td>{{ $detail->nomor_SHM ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nama Berdasarkan SHM</th>
            <td>{{ $detail->nama_SHM ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nomor Sporadik</th>
            <td>{{ $detail->nomor_sporadik ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nama Berdasarkan Sporadik</th>
            <td>{{ $detail->nama_sporadik ?? '-' }}</td>
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
            <th>Status PBB</th>
            <td>
                @php
                    $tahunSekarang = now()->year;
                    $pbbTahunIni = $detail->pbb->where('tahun', $tahunSekarang)->first();
                @endphp

                @if ($pbbTahunIni)
                    <span class="badge {{ $pbbTahunIni->status == 'lunas' ? 'bg-success' : 'bg-danger' }}">
                        {{ ucfirst($pbbTahunIni->status) }}
                    </span>
                @else
                    <span class="text-muted">Belum ada data tahun {{ $tahunSekarang }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Status Kepemilikan</th>
            <td>{{ $detail->status_kepemilikan ?? '-' }}</td>
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
    {{-- Tambahan: Data Riwayat Kepemilikan --}}
        @php
            $riwayatList = $detail->lahan->riwayatKepemilikan ?? collect();
        @endphp

        @if ($riwayatList->isNotEmpty())
            <div class="section-title">Riwayat Kepemilikan Lahan {{ $index + 1 }}</div>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pemilik Sebelumnya</th>
                        <th>Nama Pemilik Sesudah</th>
                        <th>Tanggal Ganti</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayatList as $rIndex => $riwayat)
                        <tr>
                            <td>{{ $rIndex + 1 }}</td>
                            <td>{{ $riwayat->petaniSebelum->nama ?? '-' }}</td>
                            <td>{{ $riwayat->petaniSesudah->nama ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($riwayat->tanggal_ganti)->format('d-m-Y') }}</td>
                            <td>{{ $riwayat->keterangan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-style: italic; margin-top: -10px; margin-bottom: 15px;">
                Tidak ada riwayat kepemilikan untuk lahan ini.
            </p>
        @endif

    <div class="signature">
        <p>Mengetahui,</p>
        <strong>{{ Auth::user()->nama }}</strong><br>
    </div>
</body>

</html>
