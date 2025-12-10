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
    <title>Laporan Catatan Saldo</title>

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
            margin: 5px 0 10px 0;
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

        th.text-center,
        td.text-center {
            text-align: center;
        }

        td.text-end {
            text-align: right;
        }

        .no-border th,
        .no-border td {
            border: none;
        }

        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin: 20px 0 8px 0;
            text-transform: uppercase;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }

        .card-summary {
            width: 100%;
            margin-bottom: 15px;
        }

        .card-summary td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <table>
            <tr>
                <td width="15%" style="text-align:center;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo">
                </td>
                <td width="85%" style="text-align:center; transform: translateX(-40px);">
                    <h2><strong>KOPERASI SAWIT MAKMUR</strong></h2>
                    <h3>KABUPATEN TANAH LAUT</h3>
                    <h4>KALIMANTAN SELATAN</h4>
                    <p style="font-size: 11px; margin:2px 0 0;">Alamat: Jl. A. Yani Kel. Sarang Halang RT. 04 Kec.
                        Pelaihari</p>
                    <p style="font-size: 11px; margin:0;">Email: <strong>kop.sm.13@gmail.com</strong></p>
                </td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>

    <h2 style="margin-top: 10px; margin-bottom: 20px;"><strong>LAPORAN CATATAN SALDO</strong></h2>

    {{-- CARD SUMMARY --}}
    <div class="section-title">RINGKASAN</div>
    <table cellpadding="5" cellspacing="0"
        style="border-collapse: collapse; width: 100%; text-align: center; border: 1px solid #000;">
        <!-- Baris 1: Judul utama -->
        <tr style="font-weight: bold;">
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">Total Nominal</td>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">Total Petani</td>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">Sudah Mengambil</td>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">Belum Mengambil</td>
            <td colspan="2" style="border: 1px solid #000; text-align: center;">Metode</td>
        </tr>

        <!-- Baris 2: Subheader metode -->
        <tr style="font-weight: bold;">
            <td style="border: 1px solid #000; text-align: center;">Cash</td>
            <td style="border: 1px solid #000; text-align: center;">Transfer</td>
        </tr>

        <!-- Baris 3: Jumlah orang -->
        <tr>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;"> Rp {{ number_format($stat['total_nominal'] ?? 0, 0, ',', '.')}}</td>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">{{ $stat['total_petani'] ?? 0 }}</td>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">{{ $stat['total_sudah'] ?? 0 }}</td>
            <td rowspan="2" style="border: 1px solid #000; text-align: center;">{{ $stat['total_belum'] ?? 0 }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $stat['jumlah_cash'] ?? 0 }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $stat['jumlah_transfer'] ?? 0 }}</td>
        </tr>

        <!-- Baris 4: Nominal -->
        <tr>
            <td style="border: 1px solid #000; text-align: right;">Rp {{ number_format($stat['nominal_cash'] ?? 0, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">Rp {{ number_format($stat['nominal_transfer'] ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- Tabel Saldo per Petani --}}
    <div class="section-title">Data Saldo per Petani</div>
    <table>
        <thead>
            <tr class="text-center">
                <th style="text-align: center">No</th>
                <th style="text-align: center">Nama Petani</th>
                <th style="text-align: center">No Plasma</th>
                <th style="text-align: center">Desa</th>
                <th style="text-align: center">Tahun Tanam</th>
                <th style="text-align: center">Luasan Total (Ha)</th>
                <th style="text-align: center">Periode</th>
                <th style="text-align: center">Nominal</th>
                <th style="text-align: center">Sisa</th>
                <th style="text-align: center">Metode</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataSaldo as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row->nama_petani }}</td>
                    <td class="text-center">{{ $row->nomor_plasma }}</td>
                    <td class="text-center">{{ $row->nama_desa }}</td>
                    <td class="text-center">{{ $row->tahun_tanam }}</td>
                    <td class="text-center">{{ number_format($row->luasan, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $row->periode }}</td>
                    <td class="text-end">Rp {{ number_format($row->total_nominal, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($row->sisa, 0, ',', '.') }}</td>
                    <td class="text-center">{{ ucfirst($row->status_metode) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <p>Mengetahui,</p>
        <br><br>
        <strong>{{ Auth::user()->nama }}</strong>
    </div>

</body>

</html>
