<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index() {
        $users = User::where('role', 'admin')->orderBy('created_at', 'desc')->get();
        return view('admin.users.data-admin.index', compact('users'));
    }

    public function create() {
        return view('admin.users.data-admin.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'aktif',
            'first_login' => true
        ]);

        LogHelper::create(auth()->id(), 'Kelola Pengguna', 'Admin baru ditambahkan: ' . $user->name);

        return redirect()->route('admin.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit($id) {
        $user = User::where('role', 'admin')->findOrFail($id);
        return view('admin.users.data-admin.edit', compact('user'));
    }

    public function update(Request $request, $id) {
        $user = User::where('role', 'admin')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed'
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email']
        ];

        // Admin bisa ganti password user lain tanpa password lama
        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
            $data['first_login'] = true;
        }

        $user->update($data);

        LogHelper::update(auth()->id(), 'Kelola Pengguna', 'Data admin diupdate: ' . $user->name);

        return redirect()->route('admin.index')->with('success', 'Data admin berhasil diupdate.');
    }

    public function destroy($id) {
        $user = User::where('role', 'admin')->findOrFail($id);

        // Cek jika admin terakhir
        if (User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        // Cek jika menghapus diri sendiri
        if ($user->id == auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();

        LogHelper::delete(auth()->id(), 'Kelola Pengguna', 'Admin dihapus: ' . $name);

        return redirect()->route('admin.index')->with('success', 'Admin berhasil dihapus.');
    }

    /**
     * Reset password admin
     */
    public function resetPassword($id) {
        $user = User::where('role', 'admin')->findOrFail($id);

        // Cek jika mencoba reset password sendiri
        if ($user->id == auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat reset password sendiri. Gunakan menu Ganti Password.');
        }

        // Generate password random yang aman
        // Format: 2 huruf besar + 6 angka + 2 huruf kecil (contoh: AB123456cd)
        $newPassword = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));

        // Update password dan set first_login
        $user->update([
            'password' => Hash::make($newPassword),
            'first_login' => true
        ]);

        // Log aktivitas
        LogHelper::update(
            auth()->id(), 
            'Kelola Pengguna', 
            'Password admin direset: ' . $user->name . ' oleh ' . auth()->user()->name
        );

        // Return dengan password baru (akan ditampilkan di modal)
        return redirect()->route('admin.index')->with([
            'success' => 'Password berhasil direset.',
            'new_password' => $newPassword,
            'reset_user_name' => $user->name,
            'reset_user_email' => $user->email
        ]);
    }
}
