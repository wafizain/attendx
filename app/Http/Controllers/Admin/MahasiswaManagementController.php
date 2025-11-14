<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBiometrik;
use App\Models\Prodi;
use App\Models\Kelas;
use App\Models\KelasMember;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MahasiswaExport;
use App\Imports\MahasiswaImport;

class MahasiswaManagementController extends Controller
{
    /**
     * Display a listing of mahasiswa
     */
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['prodi', 'kelas', 'user', 'kelasMembers.kelas']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by prodi
        if ($request->filled('prodi')) {
            $query->where('id_prodi', $request->prodi);
        }

        // Filter by angkatan
        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('id_kelas', $request->kelas);
        }

        // Filter by status akademik
        if ($request->filled('status')) {
            $query->where('status_akademik', $request->status);
        }

        // Filter: belum enrol fingerprint
        if ($request->boolean('no_fp')) {
            $query->where('fp_enrolled', 0);
        }

        // Filter: belum punya akun login
        if ($request->boolean('no_account')) {
            $query->whereNull('id_user');
        }

        // Sort: default to earliest input first
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder)->orderBy('id', 'asc');

        $mahasiswaList = $query->paginate(30)->withQueryString();

        // Data untuk filter
        $prodiList = Prodi::orderBy('nama')->get();
        $kelasList = Kelas::orderBy('kode')->get();
        $angkatanList = Mahasiswa::select('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');

        // Statistik
        $statistik = [
            'total' => Mahasiswa::count(),
            'aktif' => Mahasiswa::aktif()->count(),
            'cuti' => Mahasiswa::cuti()->count(),
            'lulus' => Mahasiswa::lulus()->count(),
            'do' => Mahasiswa::do()->count(),
            'with_account' => Mahasiswa::whereNotNull('id_user')->count(),
            'biometric_enrolled' => Mahasiswa::where('fp_enrolled', 1)->count(),
        ];

        return view('admin.mahasiswa.index', compact(
            'mahasiswaList',
            'prodiList',
            'kelasList',
            'angkatanList',
            'statistik'
        ));
    }

    /**
     * Show the form for creating a new mahasiswa
     */
    public function create()
    {
        $prodiList = Prodi::orderBy('nama')->get();
        $kelasList = Kelas::where('status', 1)->orderBy('kode')->get();

        return view('admin.mahasiswa.create', compact('prodiList', 'kelasList'));
    }

    /**
     * Store a newly created mahasiswa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|unique:mahasiswa,nim|regex:/^[A-Za-z0-9\-_.]{3,32}$/',
            'nama' => 'required|min:3|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'id_prodi' => 'required|exists:program_studi,id',
            'id_kelas' => 'nullable|exists:kelas,id',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'add_to_class' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $fotoPath = null;

            // Upload foto jika ada
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('mahasiswa/foto', 'public');
            }

            // Generate password otomatis
            $passwordPlain = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));

            // Generate username unik berbasis nama/nim
            $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $validated['nama'])));
            if (empty($base)) {
                $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $validated['nim']));
            }
            $candidate = substr($base, 0, 20);
            $suffix = 0;
            while (DB::table('users')->where('username', $candidate)->exists()) {
                $suffix++;
                $candidate = substr($base, 0, max(1, 20 - strlen((string)$suffix))) . $suffix;
            }
            $generatedUsername = $candidate ?: 'user'.time();

            // Create user account otomatis
            $user = User::create([
                'name' => $validated['nama'],
                'username' => $generatedUsername,
                'no_induk' => strtoupper($validated['nim']),
                'email' => $validated['email'],
                'password' => Hash::make($passwordPlain),
                'role' => 'mahasiswa',
                'status' => 'aktif',
                'first_login' => true,
            ]);

            // Create mahasiswa
            $mahasiswa = Mahasiswa::create([
                'id_user' => $user->id,
                'nim' => strtoupper($validated['nim']),
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'id_prodi' => $validated['id_prodi'],
                'id_kelas' => $validated['id_kelas'],
                'angkatan' => $validated['angkatan'],
                'status_akademik' => 'aktif',
                'alamat' => $validated['alamat'],
                'foto_path' => $fotoPath,
                'password_plain' => $passwordPlain,
            ]);

            // Tambahkan ke kelas jika kelas dipilih
            if ($validated['id_kelas']) {
                KelasMember::create([
                    'id_kelas' => $validated['id_kelas'],
                    'nim' => $mahasiswa->nim,
                    'tanggal_masuk' => now(),
                    'keterangan' => 'Ditambahkan saat registrasi mahasiswa',
                ]);
            }

            // Log activity
            LogHelper::create(auth()->id(), 'Kelola Pengguna', 'Mahasiswa baru ditambahkan: ' . $validated['nama'] . ' (NIM: ' . $validated['nim'] . ')');

            DB::commit();

            return redirect()->route('mahasiswa.index')->with([
                'success' => 'Mahasiswa berhasil ditambahkan.',
                'show_password' => true,
                'mahasiswa_name' => $validated['nama'],
                'mahasiswa_username' => $generatedUsername,
                'mahasiswa_password' => $passwordPlain
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified mahasiswa
     */
    public function show($id)
    {
        $mahasiswa = Mahasiswa::with([
            'prodi',
            'kelas',
            'user',
            'kelasMembers.kelas',
            'biometrikAktif'
        ])->findOrFail($id);

        // Force refresh user relationship to get latest data
        if ($mahasiswa->user) {
            $mahasiswa->user->refresh();
        }

        // Statistik
        $statistik = [
            'total_kelas' => $mahasiswa->kelasMembers()->count(),
            'kelas_aktif' => $mahasiswa->kelasMembers()->aktif()->count(),
            'total_biometrik' => $mahasiswa->biometrik()->count(),
            'biometrik_aktif' => $mahasiswa->biometrikAktif()->count(),
        ];

        return view('admin.mahasiswa.show', compact('mahasiswa', 'statistik'));
    }

    /**
     * Show the form for editing mahasiswa
     */
    public function edit($id)
    {
        $mahasiswa = Mahasiswa::with(['prodi', 'kelas', 'user'])->findOrFail($id);
        $prodiList = Prodi::orderBy('nama')->get();
        $kelasList = Kelas::where('status', 1)->orderBy('kode')->get();

        return view('admin.mahasiswa.edit', compact('mahasiswa', 'prodiList', 'kelasList'));
    }

    /**
     * Update the specified mahasiswa
     */
    public function update(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        $validated = $request->validate([
            'nim' => 'required|regex:/^[A-Za-z0-9\-_.]{3,32}$/|unique:mahasiswa,nim,' . $id,
            'nama' => 'required|min:3|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . ($mahasiswa->id_user ?? 'NULL') . ',id',
            'id_prodi' => 'required|exists:program_studi,id',
            'id_kelas' => 'nullable|exists:kelas,id',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'alamat' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Check if class is being changed
            $oldKelasId = $mahasiswa->id_kelas;
            $newKelasId = $validated['id_kelas'];
            
            $mahasiswa->update($validated);

            // Update user name and email if exists
            if ($mahasiswa->user) {
                $mahasiswa->user->update([
                    'name' => $validated['nama'],
                    'email' => $validated['email'],
                ]);
            }

            // Handle class change
            if ($oldKelasId != $newKelasId) {
                // Remove from old class if exists
                if ($oldKelasId) {
                    KelasMember::where('id_kelas', $oldKelasId)
                                ->where('nim', $mahasiswa->nim)
                                ->update(['tanggal_keluar' => now()]);
                }
                
                // Add to new class if selected
                if ($newKelasId) {
                    // Check if already a member
                    $existingMember = KelasMember::where('id_kelas', $newKelasId)
                                                 ->where('nim', $mahasiswa->nim)
                                                 ->whereNull('tanggal_keluar')
                                                 ->first();
                    
                    if (!$existingMember) {
                        KelasMember::create([
                            'id_kelas' => $newKelasId,
                            'nim' => $mahasiswa->nim,
                            'tanggal_masuk' => now(),
                            'keterangan' => 'Dipindahkan saat update data mahasiswa',
                        ]);
                    }
                }
            }

            // Log activity
            LogHelper::log('update', 'mahasiswa', $mahasiswa->id, "Mengupdate mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})");

            DB::commit();

            return redirect()->route('mahasiswa.index')
                ->with('success', 'Mahasiswa berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mengupdate mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified mahasiswa (soft delete)
     */
    public function archive($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        DB::beginTransaction();
        try {
            $nama = $mahasiswa->nama;
            $nim = $mahasiswa->nim;
            $oldStatus = $mahasiswa->status_akademik;

            // Update status to nonaktif before archiving
            $mahasiswa->update(['status_akademik' => 'nonaktif']);
            
            // Soft delete the mahasiswa
            $mahasiswa->delete();

            DB::commit();

            LogHelper::delete(auth()->id(), 'mahasiswa', "Archive mahasiswa: {$nama} (NIM: {$nim}), status: {$oldStatus} → nonaktif");

            return redirect()->route('mahasiswa.index')
                ->with('success', 'Mahasiswa berhasil dipindahkan ke arsip dan status diubah menjadi nonaktif.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to archive mahasiswa', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('mahasiswa.index')
                ->with('error', 'Gagal memindahkan mahasiswa ke arsip: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified mahasiswa (soft delete)
     */
    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        DB::beginTransaction();
        try {
            $nama = $mahasiswa->nama;
            $nim = $mahasiswa->nim;

            $mahasiswa->delete();

            LogHelper::log('delete', 'mahasiswa', $id, "Menghapus mahasiswa: {$nama} ({$nim})");

            DB::commit();

            return redirect()->route('mahasiswa.index')
                ->with('success', 'Mahasiswa berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status akademik
     */
    public function toggleStatus(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        // Prevent changing final status
        if (in_array($mahasiswa->status_akademik, ['lulus', 'do'])) {
            return back()->with('error', 'Status lulus/DO tidak dapat diubah.');
        }

        $validated = $request->validate([
            'status' => 'required|in:aktif,cuti,nonaktif',
        ]);

        $oldStatus = $mahasiswa->status_akademik;
        $mahasiswa->update(['status_akademik' => $validated['status']]);

        LogHelper::log(
            'update',
            'mahasiswa',
            $mahasiswa->id,
            "Mengubah status mahasiswa {$mahasiswa->nama}: {$oldStatus} → {$validated['status']}"
        );

        return back()->with('success', 'Status mahasiswa berhasil diubah.');
    }

    /**
     * Create user account for mahasiswa
     */
    public function createAccount($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        if ($mahasiswa->id_user) {
            return back()->with('error', 'Mahasiswa sudah memiliki akun login.');
        }

        DB::beginTransaction();
        try {
            $tempPassword = Str::random(12);
            
            $user = User::create([
                'name' => $mahasiswa->nama,
                'no_induk' => $mahasiswa->nim,
                'email' => $mahasiswa->email ?? $mahasiswa->nim . '@student.ac.id',
                'password' => Hash::make($tempPassword),
                'password_temp' => Hash::make($tempPassword),
                'password_temp_expires_at' => now()->addHours(24),
                'must_change_password' => 1,
                'role' => 'mahasiswa',
                'status' => 'aktif',
            ]);

            $mahasiswa->update(['id_user' => $user->id]);

            LogHelper::log(
                'create',
                'user',
                $user->id,
                "Membuat akun login untuk mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})"
            );

            DB::commit();

            return back()->with('success', "Akun berhasil dibuat. Password sementara: <strong>{$tempPassword}</strong> (berlaku 24 jam)");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat akun: ' . $e->getMessage());
        }
    }

    /**
     * Reset password mahasiswa
     */
    public function resetPassword($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        if (!$mahasiswa->id_user) {
            return back()->with('error', 'Mahasiswa belum memiliki akun login.');
        }

        DB::beginTransaction();
        try {
            // Generate password baru
            $newPassword = strtoupper(Str::random(2)) . rand(100000, 999999) . strtolower(Str::random(2));
            
            // Update password di users
            $mahasiswa->user->update([
                'password' => Hash::make($newPassword),
                'first_login' => true,
            ]);

            // Update password_plain di mahasiswa
            $mahasiswa->update([
                'password_plain' => $newPassword,
            ]);

            LogHelper::update(
                auth()->id(),
                'Kelola Pengguna',
                'Password mahasiswa direset: ' . $mahasiswa->nama . ' (NIM: ' . $mahasiswa->nim . ') oleh ' . auth()->user()->name
            );

            DB::commit();

            return redirect()->route('mahasiswa.show', $id)->with([
                'success' => 'Password berhasil direset.',
                'new_password' => $newPassword,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reset password: ' . $e->getMessage());
        }
    }

    /**
     * Reset username mahasiswa (generate otomatis)
     */
    public function resetUsername($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        if (!$mahasiswa->id_user) {
            return back()->with('error', 'Mahasiswa belum memiliki akun login.');
        }

        DB::beginTransaction();
        try {
            // Generate username unik berbasis nama/nim dengan timestamp dan random suffix
            $nama = $mahasiswa->nama;
            $nim = $mahasiswa->nim;
            $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $nama)));
            if (empty($base)) {
                $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $nim));
            }
            
            // Add timestamp + random to ensure uniqueness and always different
            $timestamp = date('ymdHis');
            $random = rand(100, 999);
            $candidate = substr($base, 0, 12) . '_' . $timestamp . '_' . $random;
            $suffix = 1;
            
            // Check for uniqueness
            while (DB::table('users')->where('username', $candidate)->where('id', '!=', $mahasiswa->id_user)->exists()) {
                $candidate = substr($base, 0, max(1, 12 - strlen((string)$suffix))) . '_' . $timestamp . '_' . $random . '_' . $suffix;
                $suffix++;
            }
            
            $newUsername = $candidate ?: 'user_' . time() . '_' . rand(100, 999);

            // Update username using User model
            $user = \App\Models\User::findOrFail($mahasiswa->id_user);
            $user->username = $newUsername;
            $user->save();

            // Refresh the relationship to get updated data
            $mahasiswa->refresh();
            $mahasiswa->load('user'); // Explicitly reload the user relationship

            LogHelper::update(
                auth()->id(),
                'Kelola Pengguna',
                'Username mahasiswa direset: ' . $mahasiswa->nama . ' (NIM: ' . $mahasiswa->nim . ') oleh ' . auth()->user()->name
            );

            DB::commit();

            return redirect()->back()->with('success', 'Username berhasil direset.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Reset Username Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal reset username: ' . $e->getMessage());
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        if (!$mahasiswa->id_user) {
            return back()->with('error', 'Mahasiswa tidak memiliki akun login.');
        }

        DB::beginTransaction();
        try {
            $userId = $mahasiswa->user->id;
            $mahasiswa->user->delete();
            $mahasiswa->update(['id_user' => null]);

            LogHelper::log(
                'delete',
                'user',
                $userId,
                "Menghapus akun login mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})"
            );

            DB::commit();

            return back()->with('success', 'Akun login berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }

    /**
     * Export mahasiswa
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'mahasiswa_' . date('Y-m-d_His') . '.' . $format;

        LogHelper::log('export', 'mahasiswa', null, "Export mahasiswa ke {$format}");

        return Excel::download(new MahasiswaExport($request->all()), $filename);
    }

    /**
     * Import mahasiswa
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $import = new MahasiswaImport();
            Excel::import($import, $request->file('file'));

            $count = $import->getRowCount();

            LogHelper::log('import', 'mahasiswa', null, "Import {$count} mahasiswa dari CSV");

            DB::commit();

            return back()->with('success', "Berhasil import {$count} mahasiswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $filename = 'template_import_mahasiswa.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['nim', 'nama', 'email', 'no_hp', 'id_prodi', 'angkatan', 'status_akademik', 'create_account'];
        $sample = ['2021001', 'Budi Santoso', 'budi@student.ac.id', '081234567890', '1', '2021', 'aktif', '1'];

        $callback = function() use ($columns, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,export',
            'ids' => 'required|array',
            'ids.*' => 'exists:mahasiswa,id',
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];

        DB::beginTransaction();
        try {
            switch ($action) {
                case 'delete':
                    Mahasiswa::whereIn('id', $ids)->delete();
                    LogHelper::log('delete', 'mahasiswa', null, "Bulk delete " . count($ids) . " mahasiswa");
                    $message = count($ids) . " mahasiswa berhasil dihapus.";
                    break;

                case 'activate':
                    Mahasiswa::whereIn('id', $ids)
                        ->whereNotIn('status_akademik', ['lulus', 'do'])
                        ->update(['status_akademik' => 'aktif']);
                    LogHelper::log('update', 'mahasiswa', null, "Bulk activate " . count($ids) . " mahasiswa");
                    $message = count($ids) . " mahasiswa berhasil diaktifkan.";
                    break;

                case 'deactivate':
                    Mahasiswa::whereIn('id', $ids)
                        ->whereNotIn('status_akademik', ['lulus', 'do'])
                        ->update(['status_akademik' => 'nonaktif']);
                    LogHelper::log('update', 'mahasiswa', null, "Bulk deactivate " . count($ids) . " mahasiswa");
                    $message = count($ids) . " mahasiswa berhasil dinonaktifkan.";
                    break;

                case 'export':
                    DB::commit();
                    return $this->export($request->merge(['ids' => $ids]));
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan aksi: ' . $e->getMessage());
        }
    }
}
