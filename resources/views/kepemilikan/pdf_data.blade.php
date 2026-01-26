<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Kepemilikan Lahan</title>
    <style>
        @page {
            margin: 30px 25px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }

        h2,
        h4 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 7px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        thead {
            display: table-header-group;
        }

        tr,
        td,
        th {
            page-break-inside: avoid !important;
        }
    </style>
</head>

<body>

    <div class="header-section">
        <h2>DATA KEPEMILIKAN LAHAN PETANI PLASMA</h2>
        <h4 style="margin-bottom: 2%; margin-top: 1%;">
            Desa: {{ $request->desa ?? 'Semua Desa' }} ||
            Tahun Tanam: {{ $request->tahun ?? 'Semua Tahun' }} ||
            Status Petani: {{ ucfirst($request->status_petani ?? 'Aktif') }} ||
            Status Kelola:
            @if ($request->filled('status_pengelolaan'))
                {{ implode(', ', (array) $request->status_pengelolaan) }}
            @else
                Semua Kelola
            @endif
        </h4>
    </div>

    @php $exclude = $request->exclude ?? []; @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>

                @if (!in_array('nomor_plasma', $exclude))
                    <th style="width: 7%;">Nomor Plasma</th>
                @endif

                @if (!in_array('nomor_koperasi', $exclude))
                    <th style="width: 7%;">Nomor Koperasi</th>
                @endif

                @if (!in_array('nama', $exclude))
                    <th style="width: 13%;">Petani Sekarang</th>
                @endif

                @if (!in_array('riwayat', $exclude))
                    <th style="width: 13%;">Petani Sebelum</th>
                @endif

                @if (!in_array('desa', $exclude))
                    <th style="width: 13%;">Desa</th>
                @endif

                @if (!in_array('tahun', $exclude))
                    <th style="width: 8%;">Tahun Tanam</th>
                @endif

                @if (!in_array('kode', $exclude))
                    <th style="width: 8%;">Kode</th>
                @endif

                @if (!in_array('kavling', $exclude))
                    <th style="width: 8%;">No. Kavling</th>
                @endif

                @if (!in_array('luas_peta', $exclude))
                    <th style="width: 10%;">Luas Lapangan</th>
                @endif

                @if (!in_array('luas_surat', $exclude))
                    <th style="width: 10%;">Luas Surat</th>
                @endif

                @if (!in_array('shm', $exclude))
                    <th style="width: 16%;">No. SHM</th>
                @endif

                @if (!in_array('sporadik', $exclude))
                    <th style="width: 14%;">No. Sporadik</th>
                @endif

                @if (!in_array('status_pengelolaan', $exclude))
                    <th style="width: 9%;">Status Kelola</th>
                @endif
            </tr>
        </thead>

        <tbody>

            @php $noGlobal = 1; @endphp

            @foreach ($kepemilikan as $k)
                @php
                    $details = $k->detailKepemilikan->values();

                    if (
                        $k->status_kepemilikan === 'berhenti' &&
                        $details->every(fn($d) => $d->status_pengelolaan === 'Perusahaan')
                    ) {
                        continue;
                    }
                @endphp

                @foreach ($details as $i => $detail)
                    @php
                        $lahan = $detail->lahan;
                        $desaNama = $lahan->desa->desa ?? '-';
                        $tahunNama = $lahan->tahunTanam->tahun ?? '-';
                        $luasPeta = number_format((float) ($lahan->luas_peta ?? 0), 2) . ' M²';
                        $luasSurat = number_format((float) ($detail->luas_surat ?? 0), 2) . ' M²';
                    @endphp

                    <tr>
                        <td>{{ $i === 0 ? $noGlobal++ : '' }}</td>
                        @if (!in_array('nomor_plasma', $exclude))
                            <td>{{ $i === 0 ? $k->petani->nomor_anggota_plasma ?? '-' : '' }}</td>
                        @endif

                        @if (!in_array('nomor_koperasi', $exclude))
                            <td>{{ $i === 0 ? $k->petani->nomor_anggota_koperasi ?? '-' : '' }}</td>
                        @endif

                        @if (!in_array('nama', $exclude))
                            <td style="text-align: left;">
                                {{ $i === 0 ? $k->petani->nama ?? '-' : '' }}
                            </td>
                        @endif

                        @if (!in_array('riwayat', $exclude))
                            @php
                                $riwayat = $detail->lahan->riwayatKepemilikan
                                    ->sortBy('id')
                                    ->pluck('petaniSebelum.nama')
                                    ->filter()
                                    ->values();

                                $isKosong = $riwayat->isEmpty();
                            @endphp

                            <td style="text-align: {{ $isKosong ? 'center' : 'left' }};">
                                @if ($isKosong)
                                    -
                                @else
                                    @foreach ($riwayat as $index => $nama)
                                        {{ $index + 1 }}. {{ $nama }}<br>
                                    @endforeach
                                @endif
                            </td>
                        @endif

                        @if (!in_array('desa', $exclude))
                            <td>{{ $desaNama }}</td>
                        @endif

                        @if (!in_array('tahun', $exclude))
                            <td>{{ $tahunNama }}</td>
                        @endif

                        @if (!in_array('kode', $exclude))
                            <td>{{ $detail->kode_lahan ?? '-' }}</td>
                        @endif

                        @if (!in_array('kavling', $exclude))
                            <td>{{ $detail->nomor_kavling ?? '-' }}</td>
                        @endif

                        @if (!in_array('luas_peta', $exclude))
                            <td>{{ $lahan->luas_peta ? $luasPeta : '-' }}</td>
                        @endif

                        @if (!in_array('luas_surat', $exclude))
                            <td>{{ $detail->luas_surat ? $luasSurat : '-' }}</td>
                        @endif

                        @if (!in_array('shm', $exclude))
                            <td>{{ $detail->nomor_SHM ?? '-' }}</td>
                        @endif

                        @if (!in_array('sporadik', $exclude))
                            <td>{{ $detail->nomor_sporadik ?? '-' }}</td>
                        @endif

                        @if (!in_array('status_pengelolaan', $exclude))
                            <td>{{ $detail->status_pengelolaan ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>

</html>
