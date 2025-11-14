<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FingerprintService;
use App\Models\Device;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FingerprintRegistrationController extends Controller
{
    protected $fingerprintService;

    public function __construct(FingerprintService $fingerprintService)
    {
        $this->fingerprintService = $fingerprintService;
    }

    /**
     * Halaman registrasi fingerprint untuk mahasiswa
     */
    public function index()
    {
        $user = Auth::user();
        
        // Cek apakah user adalah mahasiswa
        if ($user->role !== 'mahasiswa') {
            return redirect()->back()->with('error', 'Hanya mahasiswa yang dapat registrasi fingerprint');
        }

        // Get data mahasiswa
        $mahasiswa = DB::table('mahasiswa')
            ->where('id_user', $user->id)
            ->first();

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan');
        }

        // Get available devices
        $devices = Device::where('status', 'active')
            ->whereIn('device_type', ['fingerprint', 'hybrid'])
            ->get();

        return view('mahasiswa.fingerprint.register', compact('mahasiswa', 'devices'));
    }

    /**
     * Proses registrasi fingerprint dari device
     * API endpoint untuk device mengirim data registrasi
     * 
     * POST /api/device/register-fingerprint
     */
    public function registerFromDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|exists:devices,device_id',
            'nim' => 'required|string',
            'fingerprint_id' => 'required|integer',
            'fingerprint_template' => 'required|string',
            'finger_number' => 'required|integer|in:1,2',
        ]);

        try {
            // Cari device
            $device = Device::where('device_id', $request->device_id)->first();

            // Cari mahasiswa berdasarkan NIM
            $mahasiswa = DB::table('mahasiswa')
                ->where('nim', $request->nim)
                ->first();

            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa dengan NIM tersebut tidak ditemukan'
                ], 404);
            }

            // Registrasi fingerprint
            $success = $this->fingerprintService->registerFingerprint(
                $mahasiswa->id_mahasiswa,
                $request->fingerprint_id,
                $request->fingerprint_template,
                $request->finger_number,
                $device->id
            );

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal registrasi fingerprint. ID mungkin sudah digunakan.'
                ], 400);
            }

            // Log activity
            LogHelper::create(
                $mahasiswa->id_user,
                'Registrasi Fingerprint',
                "Fingerprint jari ke-{$request->finger_number} berhasil didaftarkan"
            );

            return response()->json([
                'success' => true,
                'message' => 'Fingerprint berhasil didaftarkan',
                'data' => [
                    'mahasiswa' => [
                        'nim' => $mahasiswa->nim,
                        'nama' => $mahasiswa->nama,
                    ],
                    'fingerprint_id' => $request->fingerprint_id,
                    'finger_number' => $request->finger_number,
                    'registered_at' => now()->format('Y-m-d H:i:s')
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Fingerprint registration error', [
                'error' => $e->getMessage(),
                'nim' => $request->nim
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi fingerprint'
            ], 500);
        }
    }

    /**
     * Hapus fingerprint mahasiswa
     */
    public function delete(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mahasiswa') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $mahasiswa = DB::table('mahasiswa')
            ->where('id_user', $user->id)
            ->first();

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan'
            ], 404);
        }

        $fingerNumber = $request->input('finger_number', 0);

        $success = $this->fingerprintService->deleteFingerprint(
            $mahasiswa->id_mahasiswa,
            $fingerNumber
        );

        if ($success) {
            LogHelper::delete(
                $user->id,
                'Registrasi Fingerprint',
                "Fingerprint jari ke-{$fingerNumber} dihapus"
            );

            return response()->json([
                'success' => true,
                'message' => 'Fingerprint berhasil dihapus'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus fingerprint'
        ], 500);
    }

    /**
     * Get status registrasi fingerprint mahasiswa
     */
    public function status()
    {
        $user = Auth::user();
        
        if ($user->role !== 'mahasiswa') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $mahasiswa = DB::table('mahasiswa')
            ->where('id_user', $user->id)
            ->first();

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'fingerprint_registered' => (bool) $mahasiswa->fingerprint_registered,
                'fingerprint_id_1' => $mahasiswa->fingerprint_id_1,
                'fingerprint_id_2' => $mahasiswa->fingerprint_id_2,
                'registered_at' => $mahasiswa->fingerprint_registered_at,
                'has_finger_1' => !is_null($mahasiswa->fingerprint_id_1),
                'has_finger_2' => !is_null($mahasiswa->fingerprint_id_2),
            ]
        ]);
    }

    /**
     * Get next available fingerprint ID
     * Untuk device yang perlu tahu ID berikutnya
     */
    public function getNextId()
    {
        $nextId = $this->fingerprintService->getNextFingerprintId();

        return response()->json([
            'success' => true,
            'data' => [
                'next_fingerprint_id' => $nextId
            ]
        ]);
    }
}
