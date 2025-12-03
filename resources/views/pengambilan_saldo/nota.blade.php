<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nota Pengeluaran</title>

    <style>
        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        body {
            font-family: "Courier New", monospace;
            font-size: 13px;
            margin: 0;
            padding: 20px;
            width: 21cm;
            height: 13.99cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .center {
            text-align: center;
        }

        .line {
            border-bottom: 1px solid #000;
            margin: 6px 0;
        }

        .small {
            font-size: 12px;
        }

        .checkbox-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 130px;
            margin-left: 25%;
            /* Jarak antar kelompok checkbox+label */
        }

        .checkbox-box {
            display: inline-block;
            width: 25px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 3px;
            vertical-align: middle;
        }

        .checkbox-filled {
            background-color: #000;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 3px 0 5px;
        }
    </style>
</head>
@php
    $bulanNama = [
        1 => 'JANUARI',
        2 => 'FEBRUARI',
        3 => 'MARET',
        4 => 'APRIL',
        5 => 'MEI',
        6 => 'JUNI',
        7 => 'JULI',
        8 => 'AGUSTUS',
        9 => 'SEPTEMBER',
        10 => 'OKTOBER',
        11 => 'NOVEMBER',
        12 => 'DESEMBER',
    ];

    $today = now();
    $tanggalIndonesia = $today->format('j') . ' ' . $bulanNama[intval($today->format('n'))] . ' ' . $today->format('Y');
@endphp

@php
    // Ambil bulan & tahun dari input
    [$tahun1, $bulan1] = explode('-', $bulan_awal);
    [$tahun2, $bulan2] = explode('-', $bulan_akhir);
@endphp


<body>

    {{-- HEADER --}}
    <table>
        <tr>
            <td width="15%" style="text-align:center; vertical-align:top;">
                <img src="{{ asset('logo.png') }}" alt="Logo" style="width:70px;">

            </td>

            <td width="85%" style="text-align:center; transform: translateX(-45px);">
                <div style="font-size:20px; font-weight:bold; font-family: Georgia, serif;">
                    KOPERASI SAWIT MAKMUR
                </div>

                <div class="small" style="margin-top:10px;">
                    Alamat: Jl. A. YANI RT 04 KELURAHAN SARANG HALANG, PELAIHARI
                </div>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2px solid #000; margin: 2px 0;"></div>

    <div class="section-title">BUKTI PENGELUARAN</div>


    {{-- METODE --}}
    <table style="margin-bottom: 3px; font-size: 11px; width:100%;">
        <tr>
            <td width="80%">
                <div style="text-align: center; padding-left: 20px;">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <span class="checkbox-box {{ $trx->metode == 'cash' ? 'checkbox-filled' : '' }}"></span>
                            <span>KAS</span>
                        </div>

                        <div class="checkbox-item">
                            <span class="checkbox-box {{ $trx->metode == 'transfer' ? 'checkbox-filled' : '' }}"></span>
                            <span>BANK</span>
                        </div>
                    </div>
                </div>
            </td>

            <td width="20%" style="text-align:right; white-space:nowrap;">
                NO. BUKTI: <strong>{{ $no_bukti }}</strong>
            </td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- DATA PENERIMA --}}
    <div style="font-size: 11px;">
        <table style="width:100%; margin-bottom: 4px;"> {{-- Jarak bawah --}}
            <tr>
                {{-- KIRI --}}
                <td width="50%" style="vertical-align:top; padding-bottom: 3px;">
                    <div>Penerima : {{ $p['nama_petani'] }}</div>
                    <div style="margin-top: 3px;">Alamat&nbsp;&nbsp;&nbsp;: {{ $p['alamat_petani'] }}</div>
                </td>

                {{-- TENGAH --}}
                <td width="30%" style="vertical-align:top; padding-left: 40px; padding-bottom: 3px;">
                    <div>DIBUAT OLEH:</div>
                    <div style="margin-top: 3px;">KOPERASI SAWIT MAKMUR</div>
                </td>

                {{-- KANAN --}}
                <td width="20%" style="vertical-align:top; text-align:right; padding-bottom: 3px;">
                    <div style="display: inline-block; text-align: left;">
                        <div>TANGGAL:</div>
                        <div style="font-size: 15px; margin-top: 3px;">{{ $tanggalIndonesia }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="line" style="margin-top: -5px;"></div>
    </div>

    {{-- URAIAN --}}
    <div style="margin:4px 0; font-weight:bold;">Pembayaran pendapatan petani</div>

    <table>
        <tr>
            <td width="20%">Nama</td>
            <td>: {{ $p['nama_petani'] }}</td>
        </tr>

        <tr>
            <td>No Plasma</td>
            <td>: {{ $p['no_plasma'] ?? '-' }}</td>
        </tr>

        <tr>
            <td>No Koperasi</td>
            <td>: {{ $p['no_koperasi'] }}</td>
        </tr>

        <tr>
            <td>Luas Lahan</td>
            <td>: {{ number_format($p['luas_ha'], 2, ',', '.') }} Ha</td>
        </tr>

        <tr>
            <td>Tahun Tanam</td>
            <td>: {{ $tahunTanam->tahun }}</td>
        </tr>
    </table>

    <br>

    <div style="font-weight:bold;">
        PERIODE
        {{ strtoupper($bulanNama[$bulan_awal_bulan] . ' ' . $bulan_awal_tahun) }}
        –
        {{ strtoupper($bulanNama[$bulan_akhir_bulan] . ' ' . $bulan_akhir_tahun) }}
    </div>


    <table style="margin-top:6px;">
        <tr>
            <td>BULAN {{ $bulanNama[(int) $bulan1] }} {{ $tahun1 }}</td>
            <td>: Rp {{ number_format($p['nominal_bulan_1'], 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td>BULAN {{ $bulanNama[(int) $bulan2] }} {{ $tahun2 }}</td>
            <td>: Rp {{ number_format($p['nominal_bulan_2'], 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td><strong>TOTAL</strong></td>
            <td><strong>: Rp {{ number_format($p['nominal'], 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="line" style="margin-top:10px;"></div>

    {{-- TANDA TANGAN --}}
    <table style="margin-top:15px;">
        <tr>
            <td width="33%" class="center">Dikeluarkan</td>
            <td width="33%" class="center">Dibukukan</td>
            <td width="33%" class="center">Telah Diterima</td>
        </tr>

        <tr style="height: 55px;">
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td class="center small">(____________________)</td>
            <td class="center small">(____________________)</td>
            <td class="center small">(____________________)</td>
        </tr>
    </table>

</body>

</html>
