@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Edit Pengguna</h4>

        <div class="card p-4">
            <form action="{{ route('user.update', $user->id_user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-kecil @error('nama') is-invalid @enderror"
                            id="nama" name="nama" value="{{ old('nama', $user->nama) }}"
                            placeholder="Perbarui nama" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-kecil @error('username') is-invalid @enderror"
                            id="username" name="username" value="{{ old('username', $user->username) }}"
                            placeholder="Perbarui username" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control text-kecil @error('email') is-invalid @enderror"
                            id="email" name="email" value="{{ old('email', $user->email) }}"
                            placeholder="Perbarui email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role"
                            class="form-select text-kecil @error('role') is-invalid @enderror"
                            {{ $user->id_user == auth()->user()->id_user || ($user->role == 'super_admin' && $superAdminCount == 1) ? 'disabled' : '' }}
                            required>
                            <option value="" disabled hidden>Pilih role</option>
                            <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>
                                Super Admin</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin
                            </option>
                        </select>

                        @if ($user->id_user == auth()->user()->id_user || ($user->role == 'super_admin' && $superAdminCount == 1))
                            <input type="hidden" name="role" value="{{ $user->role }}">
                        @endif

                        @if ($user->role == 'super_admin' && $superAdminCount == 1)
                            <small class="text-muted-small">
                                Role tidak dapat diubah karena ini satu-satunya Super Admin.
                            </small>
                        @endif

                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            Password <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small>
                        </label>
                        <div class="password-wrapper position-relative">
                            <input type="password" class="form-control text-kecil @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="Perbarui password jika perlu">
                            <span id="toggle-password" class="password-toggle">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted-small">Password minimal 8 karakter</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <div class="password-wrapper position-relative">
                            <input type="password" class="form-control text-kecil" id="password_confirmation"
                                name="password_confirmation" placeholder="Ulangi password">
                            <span id="toggle-confirm-password" class="password-toggle">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Perbarui</button>
                    <a href="{{ route('user.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');

            // Hanya aktifkan Choices jika dropdown tidak di-disable
            if (!roleSelect.disabled) {
                new Choices(roleSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Pilih role',
                    allowHTML: true
                });
            }
        });
    </script>

    {{-- Script toggle password --}}
    <script>
        const toggles = [{
                btn: 'toggle-password',
                input: 'password'
            },
            {
                btn: 'toggle-confirm-password',
                input: 'password_confirmation'
            }
        ];

        toggles.forEach(({
            btn,
            input
        }) => {
            const toggleButton = document.getElementById(btn);
            const inputField = document.getElementById(input);

            toggleButton.addEventListener('click', function() {
                const isHidden = inputField.getAttribute('type') === 'password';
                inputField.setAttribute('type', isHidden ? 'text' : 'password');
                this.innerHTML = isHidden ?
                    '<i class="fas fa-eye"></i>' // mata terbuka → terlihat
                    :
                    '<i class="fas fa-eye-slash"></i>'; // mata silang → disembunyikan
            });
        });
    </script>

    <style>
        /* Tambahan biar dropdown disabled tampil lembut */
        select:disabled {
            background-color: #f5f5f5 !important;
            color: #6c757d !important;
            border-color: #ddd !important;
            cursor: not-allowed;
        }
    </style>
@endsection
