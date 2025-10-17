@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

@section('content')
<div class="container-fluid px-4 mt-5">
    <h4 class="mt-4 text-brown">Edit User</h4>

    <div class="card p-4">
        <form action="{{ route('user.update', $user->id_user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control text-kecil @error('nama') is-invalid @enderror" id="nama"
                    name="nama" value="{{ old('nama', $user->nama) }}" placeholder="Perbarui nama lengkap" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control text-kecil @error('username') is-invalid @enderror" id="username"
                    name="username" value="{{ old('username', $user->username) }}" placeholder="Perbarui username" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control text-kecil @error('email') is-invalid @enderror" id="email"
                    name="email" value="{{ old('email', $user->email) }}" placeholder="Perbarui email" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small></label>
                <input type="password" class="form-control text-kecil @error('password') is-invalid @enderror" id="password"
                    name="password" placeholder="Perbarui password jika perlu">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control text-kecil" id="password_confirmation"
                    name="password_confirmation" placeholder="Ulangi password">
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="karyawan" {{ old('role', $user->role) == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">Perbarui</button>
            <a href="{{ route('user.index') }}" class="btn btn-danger">Batal</a>
        </form>
    </div>
</div>
@endsection
