<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kepemilikan Lahan</title>
    <style>
        @page {
            margin: 25px 25px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .info {
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 7px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>

    <h2>DATA KEPEMILIKAN LAHAN PETANI</h2>
    <h4>
        Desa: {{ $request->desa ?? 'Semua Desa' }} — Tahun Tanam: {{ $request->tahun ?? 'Semua Tahun' }}
    </h4>

    <div class="info">
        <p><strong>Desa:</strong> {{ $request->desa ?? 'Semua Desa' }}</p>
        <p><strong>Tahun Tanam:</strong> {{ $request->tahun ?? 'Semua Tahun' }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Anggota Plasma</th>
                <th>No. Anggota Koperasi</th>
                <th class="text-left">Nama Petani</th>
                <th>Desa</th>
                <th>Tahun Tanam</th>
                <th>No. Kavling</th>
                <th>Luas Sesuai Peta (m²)</th>
                <th>Luas Sesuai Surat (m²)</th>
                <th>No. Surat SHM</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($kepemilikan as $k)
                @php $rowspan = $k->detailKepemilikan->count(); @endphp
                @foreach ($k->detailKepemilikan as $index => $detail)
                    @php
                        $lahan = $detail->lahan;
                        $desa = $lahan->desa->desa ?? '-';
                        $tahun = $lahan->tahunTanam->tahun ?? '-';
                    @endphp
                    <tr>
                        @if ($index === 0)
                            <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $k->petani->nomor_anggota_plasma ?? '-' }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $k->petani->nomor_anggota_koperasi ?? '-' }}</td>
                            <td rowspan="{{ $rowspan }}" class="text-left">{{ $k->petani->nama ?? '-' }}</td>
                        @endif
                        <td>{{ $desa }}</td>
                        <td>{{ $tahun }}</td>
                        <td>{{ $detail->nomor_kavling ?? '-' }}</td>
                        <td>{{ number_format($lahan->luas_peta ?? 0, 2, ',', '.') }}</td>
                        <td>{{ number_format($detail->luas_surat ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $detail->nomor_SHM ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
