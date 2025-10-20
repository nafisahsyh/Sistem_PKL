<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - SisPlasma</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/img/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
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
      <img src="{{ asset('storage/img/logo.png') }}" alt="Logo SisPlasma" class="login-logo mb-3">
      <h3 class="fw-bold mb-4">Ubah Kata Sandi</h3>

      {{-- Alert error --}}
      @if($errors->any())
          <div class="alert alert-danger">
              {{ $errors->first() }}
          </div>
      @endif

      <form action="{{ route('password.update') }}" method="POST">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ $email }}">

          <div class="form-group mb-3">
              <i class="fas fa-user"></i>
              <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email', request('email')) }}" required>
          </div>

          <div class="form-group mb-3">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
          </div>

          <div class="form-group mb-3">
              <i class="fas fa-lock"></i>
              <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
          </div>

          <button type="submit" class="btn-login">Ubah Kata Sandi</button>

          <div class="back-login mt-3">
              <a href="{{ route('login') }}">Kembali ke login</a>
          </div>
      </form>
    </div>
  </div>
</body>
</html>
