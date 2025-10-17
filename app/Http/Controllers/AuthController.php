<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
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
        $user = \App\Models\User::where($loginType, $request->login)->first();

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

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus sesi
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah logout');
    }
}
