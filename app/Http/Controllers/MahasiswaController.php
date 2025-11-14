<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MahasiswaController extends Controller
{
    public function index() {
        // Join dengan tabel mahasiswa untuk mendapatkan data lengkap
        $mahasiswas = DB::table('users')
            ->join('mahasiswa', 'users.id', '=', 'mahasiswa.id_user')
            ->where('users.role', 'mahasiswa')
            ->select(
                'users.id',
                'users.email as user_email',
                'users.status as user_status',
                'users.created_at',
                'mahasiswa.*'
            )
            ->orderBy('mahasiswa.angkatan', 'desc')
            ->orderBy('mahasiswa.nama', 'asc')
            ->get();
        
        return view('admin.users.data-mahasiswa.index', compact('mahasiswas'));
    }

    public function create() {
        return view('admin.users.data-mahasiswa.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nim' => 'required|string|max:30|unique:mahasiswa,nim',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'no_hp' => 'nullable|string|max:20',
            'kelas' => 'required|string|max:20',
            'prodi' => 'required|string|max:100',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        DB::beginTransaction();
        try {
            // Generate password plain untuk ditampilkan
            $passwordPlain = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));
            
            // Buat user
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'mahasiswa',
                'status' => 'aktif',
                'first_login' => true
            ]);

            // Buat data mahasiswa
            DB::table('mahasiswa')->insert([
                'id_user' => $user->id,
                'nim' => $validated['nim'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'kelas' => $validated['kelas'],
                'prodi' => $validated['prodi'],
                'angkatan' => $validated['angkatan'],
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            LogHelper::create(auth()->id(), 'Kelola Pengguna', 'Mahasiswa baru ditambahkan: ' . $validated['nama'] . ' (NIM: ' . $validated['nim'] . ')');

            return redirect()->route('mahasiswa.index')->with([
                'success' => 'Mahasiswa berhasil ditambahkan.',
                'show_password' => true,
                'mahasiswa_name' => $validated['nama'],
                'mahasiswa_email' => $validated['email'],
                'mahasiswa_password' => $passwordPlain
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan mahasiswa: ' . $e->getMessage());
        }
    }

    public function show($id) {
        $user = User::where('role', 'mahasiswa')->findOrFail($id);
        return view('admin.users.data-mahasiswa.show', compact('user'));
    }

    public function edit($id) {
        $mahasiswa = DB::table('users')
            ->join('mahasiswa', 'users.id', '=', 'mahasiswa.id_user')
            ->where('users.id', $id)
            ->where('users.role', 'mahasiswa')
            ->select(
                'users.id',
                'users.email as user_email',
                'users.status as user_status',
                'mahasiswa.*'
            )
            ->first();
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.index')->with('error', 'Mahasiswa tidak ditemukan.');
        }
        
        return view('admin.users.data-mahasiswa.edit', compact('mahasiswa'));
    }

    public function update(Request $request, $id) {
        $user = User::where('role', 'mahasiswa')->findOrFail($id);
        $mahasiswa = DB::table('mahasiswa')->where('id_user', $id)->first();
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.index')->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'nim' => 'required|string|max:30|unique:mahasiswa,nim,' . $mahasiswa->id_mahasiswa . ',id_mahasiswa',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'no_hp' => 'nullable|string|max:20',
            'kelas' => 'required|string|max:20',
            'prodi' => 'required|string|max:100',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $userData = [
                'name' => $validated['nama'],
                'email' => $validated['email']
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['first_login'] = true;
            }

            $user->update($userData);

            // Update mahasiswa
            DB::table('mahasiswa')
                ->where('id_user', $id)
                ->update([
                    'nim' => $validated['nim'],
                    'nama' => $validated['nama'],
                    'email' => $validated['email'],
                    'no_hp' => $validated['no_hp'],
                    'kelas' => $validated['kelas'],
                    'prodi' => $validated['prodi'],
                    'angkatan' => $validated['angkatan'],
                    'updated_at' => now()
                ]);

            DB::commit();

            LogHelper::update(auth()->id(), 'Kelola Pengguna', 'Data mahasiswa diupdate: ' . $validated['nama'] . ' (NIM: ' . $validated['nim'] . ')');

            return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate mahasiswa: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        $user = User::where('role', 'mahasiswa')->findOrFail($id);
        $mahasiswa = DB::table('mahasiswa')->where('id_user', $id)->first();
        
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Cek apakah mahasiswa terdaftar di kelas
        $kelasCount = DB::table('kelas_mahasiswa')->where('mahasiswa_id', $id)->count();
        if ($kelasCount > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus mahasiswa yang masih terdaftar di kelas.');
        }

        // Cek apakah mahasiswa memiliki data absensi
        $absensiCount = DB::table('absensi')->where('mahasiswa_id', $id)->count();
        if ($absensiCount > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus mahasiswa yang sudah memiliki data absensi.');
        }

        DB::beginTransaction();
        try {
            $name = $mahasiswa->nama;
            
            // Hapus data mahasiswa terlebih dahulu
            DB::table('mahasiswa')->where('id_user', $id)->delete();
            
            // Hapus user
            $user->delete();
            
            DB::commit();

            LogHelper::delete(auth()->id(), 'Kelola Pengguna', 'Mahasiswa dihapus: ' . $name . ' (NIM: ' . $mahasiswa->nim . ')');

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Reset password mahasiswa
     */
    public function resetPassword($id) {
        $user = User::where('role', 'mahasiswa')->findOrFail($id);
        $mahasiswa = DB::table('mahasiswa')->where('id_user', $id)->first();
        
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Generate password random
        $newPassword = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));

        DB::beginTransaction();
        try {
            // Update password di users
            $user->update([
                'password' => Hash::make($newPassword),
                'first_login' => true
            ]);

            DB::commit();

            LogHelper::update(
                auth()->id(), 
                'Kelola Pengguna', 
                'Password mahasiswa direset: ' . $mahasiswa->nama . ' (NIM: ' . $mahasiswa->nim . ') oleh ' . auth()->user()->name
            );

            return redirect()->route('mahasiswa.index')->with([
                'success' => 'Password berhasil direset.',
                'new_password' => $newPassword,
                'reset_user_name' => $mahasiswa->nama,
                'reset_user_email' => $mahasiswa->email,
                'reset_user_nim' => $mahasiswa->nim
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal reset password: ' . $e->getMessage());
        }
    }
}
