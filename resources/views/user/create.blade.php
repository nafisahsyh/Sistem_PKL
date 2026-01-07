@extends('theme.default')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@section('content')
    <div class="container-fluid px-4 mt-5">
        <h4 class="mt-4 text-brown">Tambah Pengguna</h4>

        <div class="card p-4">
            <form action="{{ route('user.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-kecil @error('nama') is-invalid @enderror"
                            id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama"
                            required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-kecil @error('username') is-invalid @enderror"
                            id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username"
                            required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control text-kecil @error('email') is-invalid @enderror"
                            id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role"
                            class="form-select text-kecil @error('role') is-invalid @enderror" required>
                            <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin
                            </option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>

                        <div class="password-wrapper position-relative">
                            <input type="password"
                                class="form-control text-kecil pe-5 @error('password') is-invalid @enderror" id="password"
                                name="password" placeholder="Masukkan password" required>
                            <span id="toggle-password" class="password-toggle">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>

                        <div class="password-strength mt-1">
                            <div id="password-strength-bar"></div>
                            <small id="password-strength-label" class="text-muted-small"></small>
                        </div>

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted-small">Password minimal 8 karakter</small>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password <span
                                class="text-danger">*</span></label>

                        <div class="password-wrapper position-relative">
                            <input type="password" class="form-control text-kecil pe-5" id="password_confirmation"
                                name="password_confirmation" placeholder="Ulangi password" required>
                            <span id="toggle-confirm-password" class="password-toggle">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                </div>


                <div class="text-start mt-3">
                    <button type="submit" class="btn btn-success me-2">Simpan</button>
                    <a href="{{ route('user.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Script dropdown Choices.js --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            new Choices(roleSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Pilih Role',
                allowHTML: true
            });
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

        // CHECK KEKUATAN PASSWORD + PROGRESS BAR
        const pwdInput = document.getElementById("password");
        const bar = document.getElementById("password-strength-bar");
        const label = document.getElementById("password-strength-label");

        pwdInput.addEventListener("input", function() {
            const pwd = pwdInput.value;
            let strength = 0;

            if (pwd.length >= 8) strength++;
            if (/[A-Z]/.test(pwd)) strength++;
            if (/[a-z]/.test(pwd)) strength++;
            if (/[0-9]/.test(pwd)) strength++;
            if (/[^A-Za-z0-9]/.test(pwd)) strength++;

            let width = 0;
            let color = "";
            let text = "";

            switch (strength) {
                case 0:
                case 1:
                    width = 25;
                    color = "red";
                    text = "Password sangat lemah";
                    break;
                case 2:
                case 3:
                    width = 60;
                    color = "#d6b600";
                    text = "Password sedang";
                    break;
                case 4:
                case 5:
                    width = 100;
                    color = "green";
                    text = "Password kuat";
                    break;
            }

            bar.style.width = width + "%";
            bar.style.background = color;
            label.textContent = pwd ? text : "";
        });
    </script>
@endsection
