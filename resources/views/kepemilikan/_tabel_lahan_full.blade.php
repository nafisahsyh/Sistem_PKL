<table class="table table-sm table-bordered mb-0">
    <tbody>
        <tr>
            <th class="text-normal text-start ps-3">Status Kelola</th>
            <td class="text-normal-sm text-start ps-3">
                <span
                    class="badge 
                    @if ($detail->status_pengelolaan == 'KSM') bg-success
                    @elseif ($detail->status_pengelolaan == 'Mandiri') bg-primary
                    @elseif ($detail->status_pengelolaan == 'Perusahaan') bg-warning text-dark
                    @else bg-secondary @endif">
                    {{ $detail->status_pengelolaan ?? '-' }}
                </span>
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3" width="30%">Desa</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->desa->desa ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Kecamatan</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->desa->kecamatan->kecamatan ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Tahun Tanam</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->lahan->tahunTanam->tahun ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Kode Lahan</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->kode_lahan ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Luas Sesuai Lapangan</th>
            <td class="text-normal-sm text-start ps-3">
                {{ number_format($detail->lahan->luas_peta, 2, ',', '.') }} M²
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Nomor Kavling</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_kavling ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Luas Sesuai Surat</th>
            <td class="text-normal-sm text-start ps-3">
                {{ number_format($detail->luas_surat ?? 0, 2, ',', '.') }} M²
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Nomor SHM</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_SHM ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Nama Sesuai SHM</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->nama_SHM ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Nomor Sporadik</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_sporadik ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Nama Sesuai Sporadik</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->nama_sporadik ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Nomor PBB</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->nomor_pbb ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Jumlah PBB</th>
            <td class="text-normal-sm text-start ps-3">
                Rp {{ number_format($detail->jumlah_pbb, 2, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Riwayat Pembayaran PBB</th>
            <td class="text-normal-sm text-start ps-3">
                @php
                    $pbbList = $detail->pbb->sortByDesc('tahun');
                @endphp

                @if ($pbbList->isNotEmpty())
                    @foreach ($pbbList as $p)
                        <div class="mb-1">
                            <strong>{{ $p->tahun }}</strong> :
                            Rp {{ number_format($p->jumlah, 2, ',', '.') }}

                            <span class="badge {{ $p->status == 'lunas' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($p->status) }}
                            </span>

                            @if ($p->status == 'belum')
                                <form action="{{ route('pbb.lunas', $p->id_pbb) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success ms-2">
                                        <i class="fas fa-check"></i> Lunas
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                @else
                    <span class="text-muted">Belum ada data PBB tahunan.</span>
                @endif
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Koordinat Lahan</th>
            <td class="text-normal-sm text-start ps-3">
                {{ $detail->koordinat_x && $detail->koordinat_y ? $detail->koordinat_x . ', ' . $detail->koordinat_y : '- , -' }}
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Status Lahan</th>
            <td class="text-normal-sm text-start ps-3">
                <span class="badge {{ $detail->status_kepemilikan == 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                    {{ ucfirst(strtolower($detail->status_kepemilikan)) }}
                </span>
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Tanggal Mulai</th>
            <td class="text-normal-sm text-start ps-3">
                {{ $detail->tanggal_mulai ? \Carbon\Carbon::parse($detail->tanggal_mulai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Tanggal Selesai</th>
            <td class="text-normal-sm text-start ps-3">
                {{ $detail->tanggal_selesai ? \Carbon\Carbon::parse($detail->tanggal_selesai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Posisi Surat</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->posisi_surat ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Status Penyerahan Surat</th>
            <td class="text-normal-sm text-start ps-3">{{ $detail->status_penyerahan ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">File SHM</th>
            <td class="text-normal-sm text-start ps-3">
                @if (!empty($detail->pdf_scan_shm))
                    <a href="{{ asset('storage/' . $detail->pdf_scan_shm) }}" target="_blank"
                        class="btn btn-info btn-sm text-dark">
                        <i class="fas fa-file-pdf"></i> Lihat
                    </a>
                @else
                    <span class="text-muted">Tidak ada file</span>
                @endif
            </td>
        </tr>

        <tr>
            <th class="text-normal text-start ps-3">Peta Lahan</th>
            <td class="text-normal-sm text-start ps-3">
                @if (!empty($detail->pdf_scan_peta))
                    <a href="{{ asset('storage/' . $detail->pdf_scan_peta) }}" target="_blank"
                        class="btn btn-info btn-sm text-dark">
                        <i class="fas fa-file-pdf"></i> Lihat
                    </a>
                @else
                    <span class="text-muted">Tidak ada file</span>
                @endif
            </td>
        </tr>
    </tbody>
</table>
