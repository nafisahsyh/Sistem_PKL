<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Besar</title>

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
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

        .text-right {
            text-align: right !important;
        }

        .signature {
            width: 100%;
            text-align: right;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td width="15%" style="text-align:center; vertical-align: middle;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo">
                </td>

                <td width="85%" style="text-align:center; transform: translateX(-40px);">
                    <h2><strong>KOPERASI SAWIT MAKMUR</strong></h2>
                    <h3>KABUPATEN TANAH LAUT</h3>
                    <h4>KALIMANTAN SELATAN</h4>

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
    @php
        $judul = $tipe === 'kredit' ? 'LAPORAN BUKU BESAR KREDIT BAGI HASIL' : 'LAPORAN BUKU BESAR DEBIT PENGAMBILAN';
    @endphp

    <h3 style="margin-top: 5px; margin-bottom: 15px;">
        <strong>{{ $judul }}</strong>
    </h3>

    <table>
        <thead style="display: table-header-group;">
            <tr>
                <th>No</th>
                <th>No Plasma</th>
                <th>Nama Petani</th>
                <th>Desa</th>
                <th>Tahun Tanam</th>
                <th>Luas Lahan</th>
                <th>Periode</th>
                <th>Nominal</th>

                @if (request('tipe') == 'debit_pengambilan')
                    <th>Metode</th>
                @endif
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

            @foreach ($dataTransaksi as $i => $trx)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $trx->nomor_plasma }}</td>
                    <td class="text-left">{{ $trx->nama_petani }}</td>
                    <td>{{ $trx->nama_desa }}</td>
                    <td>{{ $trx->tahun_tanam }}</td>

                    <td>{{ number_format($trx->luasan, 2, ',', '.') }} Ha</td>

                    {{-- PERIODE --}}
                    <td class="text-left">
                        @if ($trx->bulan_awal && $trx->bulan_akhir)
                            {{ $bulanIndonesia[intval(substr($trx->bulan_awal, 5, 2))] }}
                            -
                            {{ $bulanIndonesia[intval(substr($trx->bulan_akhir, 5, 2))] }}
                            {{ substr($trx->bulan_awal, 0, 4) }}
                        @else
                            -
                        @endif
                    </td>

                    {{-- NOMINAL --}}
                    <td class="text-right">
                        Rp {{ number_format($trx->total_nominal, 2, ',', '.') }}
                    </td>

                    {{-- METODE (khusus debit) --}}
                    @if (request('tipe') == 'debit_pengambilan')
                        <td>{{ ucfirst($trx->metode) }}</td>
                    @endif
                </tr>
            @endforeach

            @if (count($dataTransaksi) == 0)
                <tr>
                    <td colspan="{{ request('tipe') == 'debit_pengambilan' ? 9 : 8 }}">
                        Tidak ada data
                    </td>
                </tr>
            @endif
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
