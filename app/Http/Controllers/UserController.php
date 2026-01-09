<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar semua user dengan search & pagination
     */
    public function index(Request $request)
    {
        $query = User::query();

    // Filter search
    if ($request->has('search') && !empty($request->search)) {
        $keyword = $request->search;

        // Ganti query lama dengan ini
        $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('username', 'like', "%{$keyword}%")
                ->orWhereRaw("REPLACE(role, '_', ' ') LIKE ?", ["%{$keyword}%"]);
        });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('user.index', compact('users'));
    }

    /**
     * Tampilkan form untuk membuat user baru
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Simpan user baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:20|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'karyawan'])],
        ]);

        User::create($request->only('nama', 'username', 'email', 'role', 'password'));

        return redirect()->route('user.index')->with('success', 'User berhasil dibuat.');
    }

    /**
     * Tampilkan form edit user
     */
    public function edit(User $user)
    {
        // Hitung total super_admin
        $superAdminCount = User::where('role', 'super_admin')->count();

        return view('user.edit', compact('user', 'superAdminCount'));
    }

    /**
     * Update user di database
     */
    public function update(Request $request, User $user)
    {
        $superAdminCount = User::where('role', 'super_admin')->count();

        // Cegah perubahan role jika ini adalah super admin terakhir
        if ($user->role === 'super_admin' && $superAdminCount === 1 && $request->role !== 'super_admin') {
            return back()->with('error', 'Tidak dapat mengubah role satu-satunya Super Admin.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id_user, 'id_user')],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id_user, 'id_user')],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'karyawan'])],
        ]);

        $user->nama = $request->nama;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user
     */
    public function destroy(User $user)
    {
        // Cegah user menghapus dirinya sendiri
        if (auth()->id() === $user->id_user) {
            return redirect()->route('user.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Jika user yang mau dihapus adalah super_admin
        if ($user->role === 'super_admin') {
            $totalSuperAdmin = User::where('role', 'super_admin')->count();

            // Kalau hanya ada 1 super_admin, larang hapus
            if ($totalSuperAdmin <= 1) {
                return redirect()->route('user.index')->with('error', 'Super Admin terakhir tidak boleh dihapus.');
            }
        }

        // Lanjut hapus jika aman
        $user->delete();

        return redirect()->route('user.index')->with('success', 'User berhasil dihapus.');
    }
}