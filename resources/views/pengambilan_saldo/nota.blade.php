<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nota Pengeluaran</title>

    <style>
        @page {
            size: 21cm 13.99cm potrait;
            margin: 0;
            /* Hapus margin printer default */
        }

        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        .sheet {
            width: 21cm;
            height: 13.99cm;
            margin: 0;
            padding: 10mm;
            box-sizing: border-box;
            border: 1px solid #ddd;
        }

        body {
            font-family: "Bookman Old Style", "Times New Roman", serif;
            font-size: 11px;
            margin: 0;
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
            font-size: 11px;
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
            font-size: 15px;
            font-weight: bold;
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

    $tanggal = \Carbon\Carbon::parse($trx->tanggal);
    $tanggalIndonesia =
        $tanggal->format('j') . ' ' . $bulanNama[intval($tanggal->format('n'))] . ' ' . $tanggal->format('Y');
@endphp

@php
    $periodeAktif = collect($periodeList)->last();
@endphp

<body>
    <div class="sheet">
        {{-- HEADER --}}
        <table>
            <tr>
                <td width="15%" style="text-align:center; vertical-align:top;">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="width:70px;">

                </td>

                <td width="85%" style="text-align:center; transform: translateX(-50px);">
                    <div style="font-size:20px; font-weight:bold; font-family: 'Bookman Old Style', serif;">
                        KOPERASI SAWIT MAKMUR
                    </div>

                    <div class="small" style="margin-top:10px; font-size:10px">
                        Alamat: Jl. A. YANI RT 04 KELURAHAN SARANG HALANG, PELAIHARI
                    </div>
                </td>
            </tr>
        </table>
        <div style="border-bottom: 2px solid #000; margin: 2px 0;"></div>

        <div class="section-title" style="font-family: 'Bookman Old Style', serif; text-align:center;">BUKTI PENGELUARAN
        </div>


        {{-- METODE --}}
        <table style="margin-bottom: 2px; font-size: 10px; width:100%;">
            <tr>
                <td width="80%">
                    <div style="text-align: center; padding-left: 20px;">
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <span class="checkbox-box">
                                    {{ $trx->metode == 'cash' ? '✔' : '' }}
                                </span>
                                <span>KAS</span>
                            </div>

                            <div class="checkbox-item">
                                <span class="checkbox-box">
                                    {{ $trx->metode == 'transfer' ? '✔' : '' }}
                                </span>
                                <span>BANK</span>
                            </div>
                        </div>
                    </div>
                </td>


                <td width="20%" style="text-align:right; white-space:nowrap; font-size: 10px;">
                    NO. BUKTI: <strong>{{ $no_bukti }}</strong>
                </td>
            </tr>
        </table>

        <div class="line"></div>

        {{-- DATA PENERIMA --}}
        <div style="font-size: 10px;">
            <table style="width:100%; margin-bottom: 2px;"> {{-- Jarak bawah --}}
                <tr>
                    {{-- KIRI --}}
                    <td width="50%" style="vertical-align:top; padding-bottom: 2px;">
                        <div>Penerima : {{ $p['nama_petani'] }}</div>
                        <div style="margin-top: 3px;">Alamat&nbsp;&nbsp;&nbsp;: {{ $p['alamat_petani'] }}</div>
                    </td>

                    {{-- TENGAH --}}
                    <td width="30%" style="vertical-align:top; padding-left: 40px; padding-bottom: 2px;">
                        <div>DIBUAT OLEH:</div>
                        <div style="margin-top: 3px;">KOPERASI SAWIT MAKMUR</div>
                    </td>

                    {{-- KANAN --}}
                    <td width="20%" style="vertical-align:top; text-align:right; padding-bottom: 2px;">
                        <div style="display: inline-block; text-align: left;">
                            <div>TANGGAL:</div>
                            <div style="font-size: 12px; margin-top: 3px;">{{ $tanggalIndonesia }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="line" style="margin-top: -5px;"></div>
        </div>

        <div style="font-size:10px; width:100%;">

            <table style="width:100%; border-collapse: collapse; margin-bottom:4px;">
                <tr>
                    <!-- KIRI -->
                    <td width="30%" style="vertical-align:top; padding-right:10px;">
                        <div style="margin-bottom:8px;">NO. REKENING</div>

                        <table style="width:50%; border-collapse: collapse; font-size:10px;">

                            <!-- No Tanda Terima -->
                            <tr>
                                <td
                                    style="border:1px solid #000; font-weight:bold; text-align:center; padding-left:1px; padding-right:1px; padding-top:4px; padding-bottom:4px">
                                    No. Tanda Terima
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="border:1px solid #000; border-top:none; text-align:center; padding:4px; font-size: 12px;">
                                    {{ $p['no_urut'] ?? '-' }}
                                </td>
                            </tr>

                            <!-- No Anggota Koperasi -->
                            <tr>
                                <td
                                    style="border:1px solid #000; font-weight:bold; text-align:center; border-top:none; padding:4px;">
                                    No. Anggota Koperasi
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="border:1px solid #000; border-top:none; text-align:center; padding:4px; font-size: 12px;">
                                    {{ $p['no_koperasi'] }}
                                </td>
                            </tr>

                            <!-- No Kartu Plasma -->
                            <tr>
                                <td
                                    style="border:1px solid #000; font-weight:bold; text-align:center; border-top:none; padding:4px; ">
                                    No. Kartu Plasma
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="border:1px solid #000; border-top:none; text-align:center; padding:8px 4px; font-size:12px; height:15px;">
                                    {{ $p['no_plasma'] ?? '-' }}
                                </td>

                            </tr>

                        </table>

                    </td>

                    <td width="50%" style="vertical-align:top; padding:0 10px;">
                        <div style="width:100%; margin-left:-65px;"> <!-- geser ke kiri sedikit -->

                            <div style="margin-bottom:5px;">URAIAN</div>
                            <div style="margin-top:3px;">Pembayaran pendapatan petani</div>
                            <!-- AN -->
                            <div style="display:flex; margin-top:3px;">
                                <div style="width:55px;">AN</div>
                                <div style="font-size:13px;">{{ $p['nama_petani'] }}</div>
                            </div>

                            <!-- Desa -->
                            <div style="display:flex;">
                                <div style="width:55px;">Desa</div>
                                <div style="font-size:13px;">{{ $p['desa'] ?? '-' }} {{ $tahunTanam->tahun }}</div>
                            </div>

                            @php
                                $count = count($periodeList);
                                $fontSize = 14; // default

                                if ($count > 2 && $count <= 5) {
                                    $fontSize = 13;
                                } elseif ($count > 5 && $count <= 8) {
                                    $fontSize = 12;
                                } elseif ($count > 8 && $count <= 10) {
                                    $fontSize = 11;
                                } elseif ($count > 10 && $count <= 12) {
                                    $fontSize = 10;
                                } elseif ($count > 12) {
                                    $fontSize = 9;
                                }
                            @endphp


                            <div style="margin-top:3px; font-size:{{ $fontSize }}px;">
                                {{ strtoupper($periodeGabungan) }}
                            </div>

                            <table style="width:100%; font-size:{{ $fontSize }}px;">
                                @foreach ($periodeList as $periode)
                                    <tr>
                                        <td style="padding-left:25px; text-decoration:underline;">
                                            {{ strtoupper($periode['label']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                        </div>
                    </td>

                    <td width="20%" style="vertical-align:top; padding-left:10px;">
                        <div style="margin-bottom:65px">JUMLAH</div>
                        <table style="width:100%; border-collapse:collapse; font-size:{{ $fontSize }}px;">
                            @foreach ($periodeList as $periode)
                                <tr>
                                    <td>
                                        <div
                                            style="display:flex; justify-content:space-between; border-bottom:1px solid #000;">
                                            <span>Rp</span>
                                            <span>{{ number_format($periode['nominal'], 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="font-weight:bold; padding-top:35px; font-size:16px;">
                                    <div style="display:flex; justify-content:space-between;">
                                        <span>Rp</span>
                                        <span>{{ number_format($grandTotal, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </td>
            </table>
            <div style="font-size: 11px; margin-top:5px;">Terbilang :</div>
        </div>

        <table style="width:100%; border-collapse:collapse; margin-top:5px; font-size:10px; text-align:center;">

            <!-- Judul Utama -->
            <tr>
                <td colspan="2" style="border:1px solid #000; padding:2px; width:20%;"></td>
                <td colspan="2" style="border:1px solid #000; padding:2px; width:20%;">DIBUKUKAN</td>
                <td colspan="2" style="border:1px solid #000; padding:2px; width:50%;">TELAH DITERIMA JUMLAH TERSEBUT
                    DI ATAS</td>
            </tr>

            <!-- Subjudul / kolom kedua -->
            <tr>
                <!-- Dikeluarkan -->
                <td style="border:1px solid #000; padding:2px; width:13%;">TANGGAL</td>
                <td style="border:1px solid #000; padding:2px; width:12%;">DIKELUARKAN</td>

                <!-- Dibukukan -->
                <td style="border:1px solid #000; padding:2px; width:10%;">TANGGAL</td>
                <td style="border:1px solid #000; padding:2px; width:20%;">PARAF</td>

                <!-- Telah Diterima -->
                <td style="border:1px solid #000; padding:2px; width:10%;">TANGGAL</td>
                <td style="border:1px solid #000; padding:2px; width:25%; font-size:8px;">TANDA TANGAN & NAMA PENERIMA
                </td>
            </tr>

            <!-- Baris kosong untuk diisi dengan titik-titik -->
            <tr style="height:60px;">
                <td style="border:1px solid #000; border-top:none; vertical-align:bottom;">.......</td>
                <td style="border:1px solid #000; border-top:none; vertical-align:bottom;">.......</td>
                <td style="border:1px solid #000; border-top:none; vertical-align:bottom;">.......</td>
                <td style="border:1px solid #000; border-top:none; vertical-align:bottom;">.......</td>
                <td style="border:1px solid #000; border-top:none; vertical-align:bottom;">.......</td>
                <td style="border:1px solid #000; border-top:none; vertical-align:bottom;">.......</td>
            </tr>
        </table>

    </div>
</body>

</html>
