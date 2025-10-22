<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Kepemilikan</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 25px;
        }

        h2, h3, h4 {
            text-align: center;
            margin-bottom: 10px;
        }

        .section-title {
            background-color: #28a745;
            color: white;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 13px;
            margin-top: 20px;
        }

        .section-title.yellow {
            background-color: #ffc107;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .no-border th, .no-border td {
            border: none;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            color: #fff;
            font-size: 11px;
        }

        .bg-success { background-color: #28a745; }
        .bg-secondary { background-color: #6c757d; }

        .signature {
            width: 100%;
            margin-top: 50px;
            text-align: right;
        }

        .signature p {
            margin-bottom: 80px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header img {
            width: 70px;
            height: auto;
            margin-bottom: 10px;
        }

        hr {
            border: 1px solid #000;
        }
    </style>
</head>
<body>

    <div class="header">
        {{-- Logo perusahaan / koperasi --}}
        <img src="{{ public_path('img/logo.png') }}" alt="Logo">
        <h2><strong>DATA KEPEMILIKAN LAHAN PLASMA</strong></h2>
        <p><em>Dicetak tanggal {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</em></p>
        <hr>
    </div>

    {{-- Data Petani --}}
    <div class="section-title">Data Petani</div>
    <table class="no-border">
        <tr><th width="30%">Nama</th><td>{{ $kepemilikan->petani->nama ?? '-' }}</td></tr>
        <tr><th>NIK</th><td>{{ $kepemilikan->petani->NIK ?? '-' }}</td></tr>
        <tr><th>Nomor Anggota Koperasi</th><td>{{ $kepemilikan->petani->nomor_anggota_koperasi ?? '-' }}</td></tr>
        <tr><th>Nomor Anggota Plasma</th><td>{{ $kepemilikan->petani->nomor_anggota_plasma ?? '-' }}</td></tr>
        <tr><th>Alamat</th><td>{{ $kepemilikan->petani->alamat ?? '-' }}</td></tr>
    </table>

    {{-- Data Kepemilikan --}}
    <div class="section-title yellow">Data Kepemilikan & Lahan</div>

    @foreach($kepemilikan->detailKepemilikan as $index => $detail)
        <h4 style="margin-top:10px;">Lahan {{ $index + 1 }}</h4>
        <table>
            <tr><th width="35%">Desa</th><td>{{ $detail->lahan->desa->desa ?? '-' }}</td></tr>
            <tr><th>Kecamatan</th><td>{{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}</td></tr>
            <tr><th>Tahun Tanam</th><td>{{ $detail->lahan->tahunTanam->tahun ?? '-' }}</td></tr>
            <tr><th>Luas Peta</th><td>{{ number_format($detail->lahan->luas_peta, 2, ',', '.') }} m²</td></tr>
            <tr><th>Nomor SHM</th><td>{{ $detail->nomor_SHM ?? '-' }}</td></tr>
            <tr><th>Nomor Sporadik</th><td>{{ $detail->nomor_sporadik ?? '-' }}</td></tr>
            <tr><th>Nomor PBB</th><td>{{ $detail->nomor_pbb ?? '-' }}</td></tr>
            <tr><th>Jumlah PBB</th><td>Rp {{ number_format($detail->jumlah_pbb, 2, ',', '.') }}</td></tr>
            <tr>
                <th>Status Kepemilikan</th>
                <td>
                    <span class="badge {{ $detail->status_kepemilikan == 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($detail->status_kepemilikan) }}
                    </span>
                </td>
            </tr>
            <tr><th>Tanggal Mulai</th><td>{{ \Carbon\Carbon::parse($detail->tanggal_mulai)->format('d-m-Y') }}</td></tr>
            <tr><th>Tanggal Selesai</th>
                <td>{{ $detail->tanggal_selesai ? \Carbon\Carbon::parse($detail->tanggal_selesai)->format('d-m-Y') : '-' }}</td>
            </tr>
        </table>
    @endforeach

    {{-- Signature --}}
    <div class="signature">
        <p>Mengetahui,</p>
        <strong>....................................</strong><br>
        <span><em>(Pihak Pengelola Koperasi)</em></span>
    </div>
</body>
</html>
