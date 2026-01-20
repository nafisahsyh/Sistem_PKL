<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SisPlasma</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.webp') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">

    <style>

    </style>
</head>

<body class="reset">
    <div class="login-container">
        <!-- Gambar kiri -->
        <div class="reset-image">
            <div class="overlay-text">
                <h2>SisPlasma</h2>
                <p>Koperasi Sawit Makmur</p>
            </div>
        </div>

        <!-- Form kanan -->
        <div class="login-form text-center">
            <img src="{{ asset('logo.webp') }}"alt="Logo SisPlasma" class="login-logo mb-3">
            <h3 class="fw-bold mb-4">Ubah Password</h3>

            {{-- Notifikasi sukses --}}
            @if (session('status'))
                <div id="flash-message" class="alert alert-success" style="transition: opacity 0.5s;">
                    {{ session('status') }}
                </div>

                <script>
                    setTimeout(function() {
                        const msg = document.getElementById('flash-message');
                        if (msg) {
                            msg.style.opacity = "0"; // mulai fade out
                            setTimeout(() => msg.remove(), 500); // hapus elemen setelah 0.5s
                        }
                    }, 3000); // tampil 3 detik sebelum fade
                </script>
            @endif

            @error('email')
                <div id="alert-error" class="alert alert-danger" style="transition: opacity .5s;">
                    {{ $message }}
                </div>
            @enderror

            <script>
                setTimeout(() => {
                    const success = document.getElementById('alert-success');
                    const error = document.getElementById('alert-error');

                    if (success) {
                        success.style.opacity = '0';
                        setTimeout(() => success.remove(), 500);
                    }

                    if (error) {
                        error.style.opacity = '0';
                        setTimeout(() => error.remove(), 500);
                    }
                }, 3000);
            </script>

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="email" class="form-control" placeholder="Email"
                        value="{{ old('email') }}" required>

                </div>
                <button type="submit" class="btn-login">Kirim Email</button>
                <div class="back-login">
                    <a href="\login">Kembali ke login</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
