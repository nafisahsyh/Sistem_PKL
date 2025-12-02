<h3 class="center">BUKTI PENGAMBILAN SALDO</h3>
<div class="center">{{ now()->format('d/m/Y H:i') }}</div>
<div class="line"></div>

<p><span class="label">Nama Petani</span>: {{ $p['nama_petani'] ?? '-' }}</p>
<p><span class="label">NIK</span>: {{ $p['nik_petani'] ?? '-' }}</p>
<p><span class="label">No Plasma</span>: {{ $p['no_plasma'] ?? '-' }}</p>
<p><span class="label">No Koperasi</span>: {{ $p['no_koperasi'] ?? '-' }}</p>
<p><span class="label">Desa</span>: {{ $desa->desa ?? '-' }}</p>
<p><span class="label">Tahun Tanam</span>: {{ $tahunTanam->tahun ?? '-' }}</p>
<p><span class="label">Luas Lahan</span>: {{ isset($p['luas_ha']) ? number_format($p['luas_ha'], 2, ',', '.') . ' Ha' : '-' }}</p>

<div class="line"></div>

<p><span class="label">Saldo Bulan Awal</span>: Rp {{ number_format($p['nominal_bulan_1'] ?? 0, 0, ',', '.') }}</p>
<p><span class="label">Saldo Bulan Akhir</span>: Rp {{ number_format($p['nominal_bulan_2'] ?? 0, 0, ',', '.') }}</p>
<p><span class="label">Total Saldo</span>: Rp {{ number_format($p['nominal'] ?? 0, 0, ',', '.') }}</p>

<div class="line"></div>

<p><span class="label">No Bukti</span>: {{ ($nextNumber ?? 1) . '.' . now()->format('d/m/Y') }}</p>
<p><span class="label">Metode</span>: {{ isset($trx->metode) ? ucfirst($trx->metode) : '-' }}</p>

<div class="line"></div>
<p class="center">Terima kasih</p>
