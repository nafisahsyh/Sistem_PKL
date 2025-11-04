<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kepemilikan Lahan</title>
    <style>
        @page { margin: 25px; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12px; }
        h2, h4 { text-align: center; margin: 0; padding: 0; }
        .info { margin-top: 20px; margin-bottom: 10px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px 7px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-left { text-align: left; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

<h2>DATA KEPEMILIKAN LAHAN PETANI</h2>
<h4>Desa: {{ $request->desa ?? 'Semua Desa' }} — Tahun Tanam: {{ $request->tahun ?? 'Semua Tahun' }}</h4>

<div class="info">
    <p><strong>Desa:</strong> {{ $request->desa ?? 'Semua Desa' }}</p>
    <p><strong>Tahun Tanam:</strong> {{ $request->tahun ?? 'Semua Tahun' }}</p>
    <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
</div>

@php
    $chunks = $kepemilikan->chunk(10); // 10 petani per halaman
    $noGlobal = 1; // nomor urut global antar halaman
@endphp

@foreach ($chunks as $chunkIndex => $chunk)
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Plasma</th>
                <th>Nomor Koperasi</th>
                <th>Nama Lengkap</th>
                <th>Desa</th>
                <th>Tahun Tanam</th>
                <th>Kode</th>
                <th>No. Kavling</th>
                <th>Luas Lapangan (M²)</th>
                <th>Luas Surat (M²)</th>
                <th>No. Surat SHM</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($chunk as $k)
                @php
                    $details = $k->detailKepemilikan->values();
                    $rowspan = $details->count();
                @endphp

                @foreach ($details as $i => $detail)
                    @php
                        $lahan = $detail->lahan ?? null;
                        $desaNama = $lahan && $lahan->desa ? ($lahan->desa->desa ?? '-') : '-';
                        $tahunNama = $lahan && $lahan->tahunTanam ? ($lahan->tahunTanam->tahun ?? '-') : '-';
                        $luasPeta = $lahan && is_numeric($lahan->luas_peta) ? (float)$lahan->luas_peta : 0;
                        $luasSurat = is_numeric($detail->luas_surat) ? (float)$detail->luas_surat : 0;
                        $noKavling = $detail->nomor_kavling ?? '-';
                        $noSHM = $detail->nomor_SHM ?? '-';
                        $kodeLahan = $detail->kode_lahan ?? '-';
                    @endphp

                    <tr>
                        @if ($i === 0)
                            {{-- Rowspan untuk kolom petani --}}
                            <td rowspan="{{ $rowspan }}">{{ $noGlobal++ }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $k->petani->nomor_anggota_plasma ?? '-'}}</td>
                            <td rowspan="{{ $rowspan }}">{{ $k->petani->nomor_anggota_koperasi ?? '-' }}</td>
                            <td rowspan="{{ $rowspan }}" class="text-left">{{ $k->petani->nama ?? '-'}}</td>
                        @endif

                        <td>{{ $desaNama }}</td>
                        <td>{{ $tahunNama }}</td>
                        <td>{{ $kodeLahan }}</td>
                        <td>{{ $noKavling }}</td>
                        <td>{{ number_format($luasPeta,2,',','.') }}</td>
                        <td>{{ number_format($luasSurat,2,',','.') }}</td>
                        <td>{{ $noSHM }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- Page break kecuali halaman terakhir --}}
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
