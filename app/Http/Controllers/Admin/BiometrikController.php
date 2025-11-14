<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBiometrik;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiometrikController extends Controller
{
    /**
     * Show biometric management page
     */
    public function index($mahasiswaId)
    {
        $mahasiswa = Mahasiswa::with(['biometrik' => function($q) {
            $q->orderBy('enrolled_at', 'desc');
        }])->findOrFail($mahasiswaId);

        $biometrikList = $mahasiswa->biometrik;

        return view('admin.mahasiswa.biometrik', compact('mahasiswa', 'biometrikList'));
    }

    /**
     * Enroll biometric
     */
    public function enroll(Request $request, $mahasiswaId)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);

        $validated = $request->validate([
            'tipe' => 'required|in:fingerprint,face',
            'ext_ref' => 'nullable|max:64',
            'template_path' => 'nullable|string',
            'face_embedding_path' => 'nullable|string',
            'quality_score' => 'nullable|integer|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Revoke old templates of same type
            MahasiswaBiometrik::where('nim', $mahasiswa->nim)
                ->where('tipe', $validated['tipe'])
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            // Create new template
            $biometrik = MahasiswaBiometrik::create([
                'nim' => $mahasiswa->nim,
                'tipe' => $validated['tipe'],
                'ext_ref' => $validated['ext_ref'] ?? null,
                'template_path' => $validated['template_path'] ?? null,
                'face_embedding_path' => $validated['face_embedding_path'] ?? null,
                'quality_score' => $validated['quality_score'] ?? null,
                'enrolled_at' => now(),
            ]);

            // Update mahasiswa flags
            if ($validated['tipe'] === 'fingerprint') {
                $mahasiswa->update([
                    'fp_enrolled' => 1,
                    'last_enrolled_at' => now(),
                ]);
            } else {
                $mahasiswa->update([
                    'face_enrolled' => 1,
                    'last_enrolled_at' => now(),
                ]);
            }

            LogHelper::log(
                'create',
                'mahasiswa_biometrik',
                $biometrik->id,
                "Enrol {$biometrik->tipe_label} mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})"
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Biometrik berhasil dienrol.',
                'data' => $biometrik,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal enrol biometrik: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke biometric template
     */
    public function revoke($id)
    {
        $biometrik = MahasiswaBiometrik::findOrFail($id);

        if ($biometrik->revoked_at) {
            return back()->with('error', 'Template sudah di-revoke.');
        }

        DB::beginTransaction();
        try {
            $biometrik->update(['revoked_at' => now()]);

            // Check if there are any active templates left
            $mahasiswa = $biometrik->mahasiswa;
            $hasActiveFp = MahasiswaBiometrik::where('nim', $mahasiswa->nim)
                ->where('tipe', 'fingerprint')
                ->whereNull('revoked_at')
                ->exists();
            $hasActiveFace = MahasiswaBiometrik::where('nim', $mahasiswa->nim)
                ->where('tipe', 'face')
                ->whereNull('revoked_at')
                ->exists();

            // Update flags
            $mahasiswa->update([
                'fp_enrolled' => $hasActiveFp ? 1 : 0,
                'face_enrolled' => $hasActiveFace ? 1 : 0,
            ]);

            LogHelper::log(
                'update',
                'mahasiswa_biometrik',
                $biometrik->id,
                "Revoke {$biometrik->tipe_label} mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})"
            );

            DB::commit();

            return back()->with('success', 'Template biometrik berhasil di-revoke.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal revoke template: ' . $e->getMessage());
        }
    }

    /**
     * Delete biometric template
     */
    public function destroy($id)
    {
        $biometrik = MahasiswaBiometrik::findOrFail($id);
        $mahasiswa = $biometrik->mahasiswa;

        DB::beginTransaction();
        try {
            $biometrik->delete();

            // Update flags
            $hasActiveFp = MahasiswaBiometrik::where('nim', $mahasiswa->nim)
                ->where('tipe', 'fingerprint')
                ->whereNull('revoked_at')
                ->exists();
            $hasActiveFace = MahasiswaBiometrik::where('nim', $mahasiswa->nim)
                ->where('tipe', 'face')
                ->whereNull('revoked_at')
                ->exists();

            $mahasiswa->update([
                'fp_enrolled' => $hasActiveFp ? 1 : 0,
                'face_enrolled' => $hasActiveFace ? 1 : 0,
            ]);

            LogHelper::log(
                'delete',
                'mahasiswa_biometrik',
                $id,
                "Hapus template {$biometrik->tipe_label} mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})"
            );

            DB::commit();

            return back()->with('success', 'Template biometrik berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }
}
