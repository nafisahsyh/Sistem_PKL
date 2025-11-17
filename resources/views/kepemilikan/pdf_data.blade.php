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

        .header-section {
            margin-bottom: 10px;
            text-align: center;
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
            vertical-align: middle;
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

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <div class="header-section">
        <h2>DATA KEPEMILIKAN LAHAN PETANI PLASMA</h2>
        <h4 style="margin-bottom: 3%; margin-top: 2%;">
            Desa: {{ $request->desa ?? 'Semua Desa' }} ||
            Tahun Tanam: {{ $request->tahun ?? 'Semua Tahun' }} ||
            Status Petani: {{ ucfirst($request->status_petani ?? 'Aktif') }} ||
            Status Kelola:
            @if ($request->filled('status_pengelolaan'))
                {{ implode(', ', (array) $request->status_pengelolaan) }}
            @else
                KSM, Mandiri
            @endif
        </h4>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Nomor Plasma</th>
                <th style="width: 7%;">Nomor Koperasi</th>
                <th style="width: 13%;">Nama Lengkap</th>
                <th style="width: 15%;">Desa</th>
                <th style="width: 8%;">Tahun Tanam</th>
                <th style="width: 8%;">Kode</th>
                <th style="width: 8%;">No. Kavling</th>
                <th style="width: 10%;">Luas Lapangan</th>
                <th style="width: 10%;">Luas Surat</th>
                <th style="width: 16%;">No. SHM</th>
                <th style="width: 9%;">Status Kelola</th>
            </tr>
        </thead>
        <tbody>
            @php $noGlobal = 1; @endphp

            @foreach ($kepemilikan as $k)
                @php
                    $details = $k->detailKepemilikan->values();

                    // skip jika petani berhenti dan semua lahannya dikelola perusahaan
                    if (
                        $k->status_kepemilikan === 'berhenti' &&
                        $details->every(fn($d) => $d->status_pengelolaan === 'Perusahaan')
                    ) {
                        continue;
                    }
                @endphp

                @foreach ($details as $i => $detail)
                    @php
                        $lahan = $detail->lahan ?? null;
                        $desaNama = $lahan->desa->desa ?? '-';
                        $tahunNama = $lahan->tahunTanam->tahun ?? '-';
                        $luasPeta = (float) ($lahan->luas_peta ?? 0);
                        $luasSurat = (float) ($detail->luas_surat ?? 0);
                        $noKavling = $detail->nomor_kavling ?? '-';
                        $noSHM = $detail->nomor_SHM ?? '-';
                        $kodeLahan = $detail->kode_lahan ?? '-';
                        $statusKelola = $detail->status_pengelolaan ?? '-';

                        $luasPetaText = number_format($luasPeta, 2) . ' M²';
                        $luasSuratText = number_format($luasSurat, 2) . ' M²';
                    @endphp

                    <tr>
                        @if ($i === 0)
                            <td>{{ $noGlobal++ }}</td>
                            <td>{{ $k->petani->nomor_anggota_plasma ?? '-' }}</td>
                            <td>{{ $k->petani->nomor_anggota_koperasi ?? '-' }}</td>
                            <td>{{ $k->petani->nama ?? '-' }}</td>
                        @else
                            {{-- baris kosong untuk data yang sama --}}
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif

                        <td>{{ $desaNama }}</td>
                        <td>{{ $tahunNama }}</td>
                        <td>{{ $kodeLahan }}</td>
                        <td>{{ $noKavling }}</td>
                        <td>{{ $luasPetaText }}</td>
                        <td>{{ $luasSuratText }}</td>
                        <td>{{ $noSHM }}</td>
                        <td>{{ $statusKelola }}</td>
                    </tr>
                @endforeach
            @endforeach

        </tbody>
    </table>

</body>

</html>
