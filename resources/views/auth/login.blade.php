<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SisPlasma</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/img/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <!-- Bagian kiri (form login) -->
        <div class="login-form text-center">
            <img src="{{ asset('storage/img/logo.png') }}" alt="Logo SisPlasma" class="login-logo mb-3">

            <h3 class="fw-bold mb-4">Login</h3>

            {{-- Pesan sukses (misal setelah logout) --}}
            @if (session('success'))
                <div id="flash-message" class="alert alert-success">
                    {{ session('success') }}
                </div>

                <script>
                    // Hapus pesan setelah 3 detik dengan fade out
                    setTimeout(function() {
                        let msg = document.getElementById('flash-message');
                        if (msg) {
                            msg.style.transition = "opacity 0.5s";
                            msg.style.opacity = "0";
                            setTimeout(() => msg.remove(), 500);
                        }
                    }, 3000);
                </script>
            @endif

            <form action="{{ url('/login') }}" method="POST" novalidate>
                @csrf

                <div class="form-group text-start">
                    <i class="fas fa-user"></i>
                    <input type="text" name="login" class="form-control" placeholder="Username/Email"
                        value="{{ old('login') }}" required autofocus>
                    @error('login')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group text-start position-relative">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control password-input"
                        placeholder="Password" required>

                    {{-- Tombol toggle mata --}}
                    <span id="toggle-password" class="password-toggle">
                        <i class="fas fa-eye-slash"></i>
                    </span>

                    @error('password')
                        <p class="error-text">{{ $message }}</p>
                    @enderror

                    @if ($errors->has('login_failed'))
                        <p class="error-text">{{ $errors->first('login_failed') }}</p>
                    @endif
                </div>

                <div class="forgot mb-3 text-end">
                    <a href="/reset">Lupa Password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            {{-- Script toggle password --}}
            <script>
                const togglePassword = document.getElementById('toggle-password');
                const password = document.getElementById('password');

                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);

                    // ubah ikon sesuai kondisi
                    this.innerHTML = type === 'password' ?
                        '<i class="fas fa-eye-slash"></i>' // mata tertutup = password disembunyikan
                        :
                        '<i class="fas fa-eye"></i>'; // mata terbuka = password terlihat
                });
            </script>
        </div>

        <!-- Bagian kanan (gambar + overlay teks) -->
        <div class="login-image">
            <div class="overlay-text">
                <h2>SisPlasma</h2>
                <p>Koperasi Sawit Makmur</p>
            </div>
        </div>
    </div>
</body>

</html>
