<div class="row">
    <div class="col-md-6 mb-3">
        <label for="nomor_anggota_plasma" class="form-label">Nomor Anggota Plasma</label>
        <input type="text" name="nomor_anggota_plasma"
            class="form-control text-kecil @error('nomor_anggota_plasma') is-invalid @enderror"
            value="{{ old('nomor_anggota_plasma') }}" placeholder="Masukkan nomor anggota plasma" required>
        @error('nomor_anggota_plasma')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="nomor_anggota_koperasi" class="form-label">Nomor Anggota Koperasi</label>
        <input type="text" name="nomor_anggota_koperasi"
            class="form-control text-kecil @error('nomor_anggota_koperasi') is-invalid @enderror"
            value="{{ old('nomor_anggota_koperasi') }}" placeholder="Masukkan nomor anggota koperasi" required>
        @error('nomor_anggota_koperasi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="NIK" class="form-label">NIK</label>
        <input type="text" name="NIK"
            class="form-control text-kecil @error('NIK') is-invalid @enderror"
            value="{{ old('NIK') }}" placeholder="Masukkan NIK 16 digit" maxlength="16" required>
        @error('NIK')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="nama" class="form-label">Nama Lengkap</label>
        <input type="text" name="nama"
            class="form-control text-kecil @error('nama') is-invalid @enderror"
            value="{{ old('nama') }}" placeholder="Masukkan nama lengkap" required>
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="alamat" class="form-label">Alamat</label>
    <textarea name="alamat" class="form-control text-kecil @error('alamat') is-invalid @enderror"
        placeholder="Masukkan alamat lengkap" required>{{ old('alamat') }}</textarea>
    @error('alamat')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status"
            class="form-select text-kecil @error('status') is-invalid @enderror" required>
            <option value="" disabled selected hidden>Pilih Status</option>
            <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>
                Tidak Aktif
            </option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="pdf_scan_ktp" class="form-label">Scan KTP (PDF)</label>
        <input type="file" name="pdf_scan_ktp"
            class="form-control text-kecil @error('pdf_scan_ktp') is-invalid @enderror"
            accept="application/pdf">
        @error('pdf_scan_ktp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted-small">Hanya file PDF, maksimal 10MB.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label for="pdf_scan_kk" class="form-label">Scan KK (PDF)</label>
        <input type="file" name="pdf_scan_kk"
            class="form-control text-kecil @error('pdf_scan_kk') is-invalid @enderror"
            accept="application/pdf">
        @error('pdf_scan_kk')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted-small">Hanya file PDF, maksimal 10MB.</small>
    </div>
</div>
