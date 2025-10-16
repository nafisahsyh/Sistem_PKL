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

            <form action="/dashboard" method="GET">
                @csrf
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Username/Email">
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>
                <div class="forgot mb-3 text-end">
                    <a href="\reset">Lupa password?</a>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
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
