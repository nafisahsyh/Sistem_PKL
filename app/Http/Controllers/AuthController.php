<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            // Kalau user sudah login, arahkan langsung sesuai role
            $user = Auth::user();
            switch ($user->role) {
                case 'super_admin':
                    return redirect()->route('dashboard.super');
                case 'admin':
                    return redirect()->route('dashboard.admin');
                default:
                    return redirect()->route('login');
            }
        }
        return view('auth.login');
    }

    /**
     * Proses login (bisa pakai email atau username)
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ], [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Cek apakah login berupa email atau username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Coba cari user
        $user = User::where($loginType, $request->login)->first();

        // Jika user ditemukan dan password cocok
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            switch ($user->role) {
                case 'super_admin':
                    return redirect()->route('dashboard.super')->with('success', 'Selamat datang Super Admin!');
                case 'admin':
                    return redirect()->route('dashboard.admin')->with('success', 'Selamat datang Admin!');
                default:
                    Auth::logout();
                    return redirect('/login')->withErrors(['login' => 'Role tidak dikenali.']);
            }
        }

        // Jika gagal login, tampilkan error di bawah input password
        return back()->withErrors([
            'password' => 'Email/Username atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus sesi
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah logout');
    }

    public function showLinkRequestForm()
    {
        return view('auth.reset');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // VALIDASI (email tidak terdaftar akan masuk sini)
        $request->validate(
            [
                'email' => 'required|email|exists:users,email',
            ],
            [
                'email.exists' => 'Email tidak terdaftar dalam sistem.',
                'email.email'  => 'Format email tidak valid.',
                'email.required' => 'Email wajib diisi.',
            ]
        );

        // Kirim link reset
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Tautan ubah kata sandi berhasil dikirim ke email Anda!');
        }

        return back()->withErrors([
            'email' => 'Gagal mengirim tautan ubah kata sandi. Silakan coba lagi.'
        ]);
    }


    public function showResetForm($token)
    {
        return view('auth.form', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function reset(Request $request)
    {

        // Validasi input
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Jangan hash di sini karena mutator di model sudah handle
                $user->password = $password;
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Kata sandi berhasil diubah!');
        } else {
            return back()->withErrors(['email' => __($status)]);
        }
    }
}
