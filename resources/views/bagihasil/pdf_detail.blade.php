<!DOCTYPE html>

@php
    $bulanIndo = [
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

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bagi Hasil Bulanan</title>

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
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

        .petani th {
            border: 0.5px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-size: 11px;
        }

        th {
            border: 0.5px solid #000;
            padding: 6px 8px;
            text-align: left;
        }

        td {
            border: 0.5px solid #000;
            padding: 4px 6px;
            font-size: 11px;
        }

        .no-border td,
        .no-border th {
            border: none;
        }

        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-top: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <table>
            <tr>
                <td width="15%" style="text-align:center; vertical-align: middle;">
                    <img src="{{ public_path('logo.webp') }}" alt="Logo">
                </td>

                <td width="85%" style="text-align:center; transform: translateX(-40px);">
                    <h2 style="margin:0;"><strong>KOPERASI SAWIT MAKMUR</strong></h2>
                    <h3 style="margin:0;">KABUPATEN TANAH LAUT</h3>
                    <h4 style="margin:0;">KALIMANTAN SELATAN</h4>

                    <p style="font-size: 11px; margin:2px 0 0;">
                        Alamat: Jl. A. Yani Kel. Sarang Halang RT. 04 Kec. Pelaihari
                    </p>
                    <p style="font-size: 11px; margin:0;">
                        Email: <strong>kop.sm.13@gmail.com</strong>
                    </p>
                </td>
            </tr>
        </table>

        <div class="kop-line"></div>
    </div>

    {{-- JUDUL --}}
    <h2 style="margin-top: 10px; margin-bottom: 20px;">
        <strong>LAPORAN BAGI HASIL BULANAN</strong>
    </h2>

    {{-- Informasi Umum --}}
    <div class="section-title">Informasi Umum</div>
    <table class="no-border">
        <tr>
            <th width="35%">Desa</th>
            <td>: {{ $bulanan->desa->desa ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tahun Tanam</th>
            <td>: {{ $bulanan->tahunTanam->tahun ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Luas Ha</th>
            <td>: {{ number_format($totalLuasHa, 2, ',', '.') }} Ha</td>
        </tr>
        <tr>
            <th>Bulan</th>
            <td>: {{ $bulanIndo[$bulanan->bulan] ?? '-' }}</td>
        </tr>

        <tr>
            <th>Tahun</th>
            <td>: {{ $bulanan->tahun }}</td>
        </tr>
        <tr>
            <th>Tanggal Bagi</th>
            <td>: {{ \Carbon\Carbon::parse($bulanan->tanggal_bagi)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Total Bagian (20%)</th>
            <td>: Rp {{ number_format($bulanan->total_bagian, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Saldo Periode Lalu</th>
            <td>: Rp {{ number_format($bulanan->sisa_saldo_snapshot, 2, ',', '.') }}</td>
        </tr>
    </table>

    {{-- Tabel Petani --}}
    <div class="section-title">Data Pembagian per Petani</div>
    <table>
        <colgroup>
            <col style="width: 4%"> <!-- No -->
            <col style="width: 22%"> <!-- Nama Petani -->
            <col style="width: 9%"> <!-- No Plasma -->
            <col style="width: 9%"> <!-- No Koperasi -->
            <col style="width: 10%"> <!-- Luas -->
            <col style="width: 15%"> <!-- Nominal -->
            <col style="width: 15%"> <!-- Saldo -->
            <col style="width: 16%"> <!-- Total -->
        </colgroup>

        <thead class="petani">
            <tr style="text-align: center">
                <th>No</th>
                <th>Nama Petani</th>
                <th>No Plasma</th>
                <th>No Koperasi</th>
                <th>Luas Lahan</th>
                <th>Nominal</th>
                <th>Saldo Periode Lalu</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($petaniData as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:left">{{ $p['nama_petani'] }}</td>
                    <td style="text-align:center">{{ $p['no_plasma'] ?? '-' }}</td>
                    <td style="text-align:center">{{ $p['no_koperasi'] ?? '-' }}</td>
                    <td style="text-align:center">{{ number_format($p['luas_ha'], 2, ',', '.') }} Ha</td>
                    <td style="text-align: right">
                        Rp {{ number_format($p['nominal_bulan_ini'], 2, ',', '.') }}
                    </td>

                    <td style="text-align: right">
                        Rp {{ number_format($p['sisa_saldo'], 2, ',', '.') }}
                    </td>

                    <td style="text-align: right">
                        Rp {{ number_format($p['total_hak'], 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <div class="signature">
        <p>Mengetahui,</p>
        <br>
        <br>
        <strong>{{ Auth::user()->nama }}</strong>
    </div>
</body>

</html>
