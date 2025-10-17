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
            $query->where('nama', 'like', "%{$keyword}%")
                ->orWhere('username', 'like', "%{$keyword}%")
                ->orWhere('role', 'like', "%{$keyword}%");
        }

        // Pagination 10 per halaman, keep query string untuk search
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

        User::create($request->only('nama','username','email','role','password'));

        return redirect()->route('user.index')->with('success', 'User berhasil dibuat.');
    }

    /**
     * Tampilkan form edit user
     */
    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }

    /**
     * Update user di database
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id_user, 'id_user')],
            'email' => ['required','email', Rule::unique('users')->ignore($user->id_user, 'id_user')],
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
        $user->delete();
        return redirect()->route('user.index')->with('success', 'User berhasil dihapus.');
    }
}
