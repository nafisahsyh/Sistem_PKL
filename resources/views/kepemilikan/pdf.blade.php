<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Kepemilikan</title>

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

        /* === KOP SURAT === */
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

        /* TABEL */
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

        /* CARD LAHAN */
        .card {
            border: 1px solid #000;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 10px;
        }

        .card-header {
            font-weight: bold;
            padding: 5px 8px;
            border-bottom: 1px solid #000;
            background: none;
            color: #000;
        }

        /* BAGIAN JUDUL */
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-top: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* TANDA TANGAN */
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

    <!-- === KOP SURAT TANPA BORDER === -->
    <div class="header">
        <table>
            <tr>
                <td width="15%" style="text-align: center;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo">
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
            <th>Status Kepemilikan</th>
            <td>: {{ ucfirst($kepemilikan->status_kepemilikan) }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>: {{ $kepemilikan->petani->alamat ?? '-' }}</td>
        </tr>
    </table>

    {{-- Data Kepemilikan & Lahan --}}
    <div class="section-title">Data Kepemilikan & Lahan</div>

    @forelse ($kepemilikan->detailKepemilikan as $index => $detail)
        <div class="card">
            <div class="card-header">Lahan {{ $index + 1 }}</div>
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
    @empty
        <p>Belum ada detail lahan untuk kepemilikan ini.</p>
    @endforelse

    <div class="signature">
        <p>Mengetahui,</p>
        <strong>{{ Auth::user()->nama }}</strong><br>
    </div>

</body>
</html>
