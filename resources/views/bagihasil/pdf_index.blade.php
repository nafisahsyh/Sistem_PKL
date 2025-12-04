<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Bagi Hasil Per Bulan</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 25px;
            color: #000;
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
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        th {
            border: 1px solid #000;
            padding: 6px 8px;
            background: #f3f3f3;
            text-align: center;
            font-weight: bold;
        }

        td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }
    </style>
</head>

<body>

    <div class="header">
        <table>
            <tr>
                <td width="15%" style="text-align:center; vertical-align: middle;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo">
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
    <h3 style="margin-top: 5px; margin-bottom: 15px;">
        <strong>LAPORAN BAGI HASIL BULANAN</strong>
    </h3>

    <table>
        <thead style="display: table-header-group;">
            <tr>
                <th>No</th>
                <th>Desa</th>
                <th>Tahun Tanam</th>
                <th>Luasan Total</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Tanggal Bagi</th>
                <th>Total Bagian (20%)</th>
            </tr>
        </thead>

        <tbody style="display: table-row-group;">
            @php
                $bulanIndonesia = [
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

            @forelse ($bulanan as $b)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-left">{{ $b->desa->desa }}</td>
                    <td>{{ $b->tahunTanam->tahun }}</td>
                    <td>{{ number_format($b->luasan_total_snapshot, 2, ',', '.') }}</td>
                    <td>{{ $bulanIndonesia[$b->bulan] }}</td>
                    <td>{{ $b->tahun }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->tanggal_bagi)->format('d-m-Y') }}</td>
                    <td>Rp {{ number_format($b->total_bagian, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>


</body>

</html>
