<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DosenController extends Controller
{
    public function index(Request $request) {
        $query = DB::table('users')
            ->join('dosen', 'users.id', '=', 'dosen.id_user')
            ->where('users.role', 'dosen')
            ->select(
                'users.id',
                'users.username',
                'users.email as user_email',
                'users.status as user_status',
                'users.created_at',
                'dosen.*'
            );

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('dosen.nama', 'like', "%{$s}%")
                  ->orWhere('dosen.nidn', 'like', "%{$s}%")
                  ->orWhere('dosen.email', 'like', "%{$s}%")
                  ->orWhere('dosen.prodi', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('dosen.status', $request->status);
        }

        if ($request->filled('jabatan')) {
            $query->where('dosen.jabatan_akademik', $request->jabatan);
        }

        $query->orderBy('users.created_at', 'asc')->orderBy('users.id', 'asc');

        $dosens = $query->paginate(30)->withQueryString();

        $jabatanList = DB::table('dosen')
            ->select('jabatan_akademik')
            ->whereNotNull('jabatan_akademik')
            ->where('jabatan_akademik', '!=', '')
            ->distinct()
            ->orderBy('jabatan_akademik')
            ->pluck('jabatan_akademik');
        
        return view('admin.users.data-dosen.index', compact('dosens', 'jabatanList'));
    }

    /**
     * Reset username dosen (generate otomatis)
     */
    public function resetUsername($id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $dosen = DB::table('dosen')->where('id_user', $id)->first();

        if (!$dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
        }

        // Generate username unik berbasis nama/email
        $nama = $dosen->nama ?: $user->name;
        $email = $dosen->email ?: $user->email;
        $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $nama)));
        if (empty($base)) {
            $local = explode('@', (string) $email)[0] ?? 'user';
            $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $local));
        }
        $candidate = substr($base, 0, 20);
        $suffix = 0;
        while (DB::table('users')->where('username', $candidate)->exists()) {
            $suffix++;
            $candidate = substr($base, 0, max(1, 20 - strlen((string)$suffix))) . $suffix;
        }
        $newUsername = $candidate ?: 'user'.time();

        $user->update(['username' => $newUsername]);

        LogHelper::update(
            auth()->id(),
            'Kelola Pengguna',
            'Username dosen direset: ' . $dosen->nama . ' (NIDN: ' . $dosen->nidn . ') oleh ' . auth()->user()->name
        );

        return redirect()->route('dosen.show', $id)->with([
            'success' => 'Username berhasil direset.',
            'new_username' => $newUsername,
        ]);
    }

    public function create() {
        return view('admin.users.data-dosen.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nidn' => 'required|string|max:20|unique:dosen,nidn',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'jabatan_akademik' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Generate password plain untuk ditampilkan dan dipakai sebagai password awal akun
            $passwordPlain = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));

            // Generate username unik berbasis nama/email
            $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $validated['nama'])));
            if (empty($base)) {
                $local = explode('@', $validated['email'])[0] ?? 'user';
                $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $local));
            }
            $candidate = substr($base, 0, 20);
            $suffix = 0;
            while (DB::table('users')->where('username', $candidate)->exists()) {
                $suffix++;
                $candidate = substr($base, 0, max(1, 20 - strlen((string)$suffix))) . $suffix;
            }
            $generatedUsername = $candidate ?: 'user'.time();

            // Buat user dengan password hasil generate
            $user = User::create([
                'name' => $validated['nama'],
                'username' => $generatedUsername,
                'email' => $validated['email'],
                'password' => Hash::make($passwordPlain),
                'role' => 'dosen',
                'status' => 'aktif',
                'first_login' => true
            ]);

            // Buat data dosen
            DB::table('dosen')->insert([
                'id_user' => $user->id,
                'nidn' => $validated['nidn'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'jabatan_akademik' => $validated['jabatan_akademik'],
                'gelar' => '',
                'status' => 'aktif',
                'password_plain' => $passwordPlain,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            LogHelper::create(auth()->id(), 'Kelola Pengguna', 'Dosen baru ditambahkan: ' . $validated['nama'] . ' (NIDN: ' . $validated['nidn'] . ')');

            return redirect()->route('dosen.index')->with([
                'success' => 'Dosen berhasil ditambahkan.',
                'show_password' => true,
                'dosen_name' => $validated['nama'],
                'dosen_username' => $generatedUsername,
                'dosen_password' => $passwordPlain
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan dosen: ' . $e->getMessage());
        }
    }

    public function show($id) {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $dosen = DB::table('dosen')->where('id_user', $id)->first();
        return view('admin.users.data-dosen.show', compact('user', 'dosen'));
    }

    public function edit($id) {
        $dosen = DB::table('users')
            ->join('dosen', 'users.id', '=', 'dosen.id_user')
            ->where('users.id', $id)
            ->where('users.role', 'dosen')
            ->select(
                'users.id',
                'users.username',
                'users.email as user_email',
                'users.status as user_status',
                'dosen.*'
            )
            ->first();
        
        if (!$dosen) {
            return redirect()->route('dosen.index')->with('error', 'Dosen tidak ditemukan.');
        }
        
        return view('admin.users.data-dosen.edit', compact('dosen'));
    }

    public function update(Request $request, $id) {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $dosen = DB::table('dosen')->where('id_user', $id)->first();
        
        if (!$dosen) {
            return redirect()->route('dosen.index')->with('error', 'Data dosen tidak ditemukan.');
        }

        $validated = $request->validate([
            'nidn' => 'required|string|max:20|unique:dosen,nidn,' . $dosen->id_dosen . ',id_dosen',
            'nama' => 'required|string|max:100',
            'password' => 'nullable|min:8|confirmed',
            'email' => 'required|email|max:100|unique:users,email,' . $id,
            'jabatan_akademik' => 'required|string|max:50',
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

            // Update dosen
            DB::table('dosen')
                ->where('id_user', $id)
                ->update([
                    'nidn' => $validated['nidn'],
                    'nama' => $validated['nama'],
                    'email' => $validated['email'],
                    'jabatan_akademik' => $validated['jabatan_akademik'],
                    'updated_at' => now()
                ]);

            DB::commit();

            LogHelper::update(auth()->id(), 'Kelola Pengguna', 'Data dosen diupdate: ' . $validated['nama'] . ' (NIDN: ' . $validated['nidn'] . ')');

            return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate dosen: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified dosen (soft delete)
     */
    public function archive($id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $dosen = Dosen::where('id_user', $id)->first();
        
        if (!$dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
        }

        // Cek apakah dosen sedang mengampu mata kuliah
        $mataKuliahCount = DB::table('mata_kuliah_pengampu')->where('dosen_id', $id)->count();
        if ($mataKuliahCount > 0) {
            return redirect()->back()->with('error', 'Tidak dapat mengarsipkan dosen yang sedang mengampu mata kuliah.');
        }

        DB::beginTransaction();
        try {
            // Soft delete dosen
            $dosen->delete();
            
            // Soft delete user account if exists
            if ($user) {
                $user->delete();
            }

            DB::commit();

            LogHelper::delete(auth()->id(), 'dosen', "Archive dosen: {$dosen->nama} (NIDN: {$dosen->nidn})");

            return redirect()->route('dosen.index')
                ->with('success', 'Dosen berhasil dipindahkan ke arsip.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to archive dosen', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memindahkan dosen ke arsip: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $dosen = DB::table('dosen')->where('id_user', $id)->first();
        
        if (!$dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
        }

        // Cek apakah dosen sedang mengampu mata kuliah
        $mataKuliahCount = DB::table('mata_kuliah_pengampu')->where('dosen_id', $id)->count();
        if ($mataKuliahCount > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus dosen yang masih mengampu mata kuliah.');
        }

        // Cek apakah dosen sedang mengampu kelas
        $kelasCount = DB::table('kelas')->where('dosen_id', $id)->count();
        if ($kelasCount > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus dosen yang masih mengampu kelas.');
        }

        DB::beginTransaction();
        try {
            $name = $dosen->nama;
            
            // Hapus data dosen terlebih dahulu
            DB::table('dosen')->where('id_user', $id)->delete();
            
            // Hapus user
            $user->delete();
            
            DB::commit();

            LogHelper::delete(auth()->id(), 'Kelola Pengguna', 'Dosen dihapus: ' . $name . ' (NIDN: ' . $dosen->nidn . ')');

            return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus dosen: ' . $e->getMessage());
        }
    }

    /**
     * Reset password dosen
     */
    public function resetPassword($id) {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $dosen = DB::table('dosen')->where('id_user', $id)->first();
        
        if (!$dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
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

            // Update password_plain di dosen
            DB::table('dosen')
                ->where('id_user', $id)
                ->update([
                    'password_plain' => $newPassword,
                    'updated_at' => now()
                ]);

            DB::commit();

            LogHelper::update(
                auth()->id(), 
                'Kelola Pengguna', 
                'Password dosen direset: ' . $dosen->nama . ' (NIDN: ' . $dosen->nidn . ') oleh ' . auth()->user()->name
            );

            return redirect()->route('dosen.index')->with([
                'success' => 'Password berhasil direset.',
                'new_password' => $newPassword,
                'reset_user_name' => $dosen->nama,
                'reset_user_email' => $dosen->email,
                'reset_user_nidn' => $dosen->nidn
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal reset password: ' . $e->getMessage());
        }
    }

    /**
     * Logging jika ada request view password dosen
     */
    public function logPasswordView(Request $request, $id)
    {
        $dosen = DB::table('dosen')->where('id_user', $id)->first();
        
        if ($dosen) {
            LogHelper::view(auth()->id(), 'Kelola Pengguna', 'Password dosen dilihat: ' . $dosen->nama . ' (NIDN: ' . $dosen->nidn . ')');
        }
        
        return response()->json(['status' => 'logged']);
    }
}
