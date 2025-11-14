<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Device;
use App\Services\FingerprintService;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeviceAbsensiController extends Controller
{
    protected $fingerprintService;

    public function __construct(FingerprintService $fingerprintService)
    {
        $this->fingerprintService = $fingerprintService;
    }

    /**
     * API untuk device mengirim data absensi (fingerprint + foto)
     * 
     * POST /api/device/absensi
     * Headers: X-Device-ID, X-Device-Key (untuk autentikasi device)
     */
    public function store(Request $request)
    {
        // Validasi device
        $device = $this->validateDevice($request);
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak valid atau tidak aktif'
            ], 401);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'fingerprint_id' => 'required|integer',
            'fingerprint_hash' => 'nullable|string',
            'kode_absensi' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048', // Max 2MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // 1. Verifikasi fingerprint
            $mahasiswaData = $this->fingerprintService->verifyFingerprint(
                $request->fingerprint_id,
                $request->fingerprint_hash
            );

            if (!$mahasiswaData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sidik jari tidak terdaftar'
                ], 404);
            }

            // 2. Cari sesi absensi berdasarkan kode (draft atau aktif)
            $sesiAbsensi = SesiAbsensi::where('kode_absensi', $request->kode_absensi)
                ->whereIn('status', ['draft', 'aktif'])
                ->first();

            if (!$sesiAbsensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi absensi tidak ditemukan atau sudah ditutup'
                ], 404);
            }
            
            // 2a. Cek apakah absensi bisa dilakukan (10 menit sebelum waktu mulai)
            $waktuSekarang = now();
            $waktuMulai = $sesiAbsensi->waktu_mulai;
            $batasAbsen = $waktuMulai->copy()->subMinutes(10); // 10 menit sebelum
            
            if ($waktuSekarang->lt($batasAbsen)) {
                $menitTersisa = $waktuSekarang->diffInMinutes($batasAbsen);
                return response()->json([
                    'success' => false,
                    'message' => "Absensi belum dibuka. Silakan coba lagi {$menitTersisa} menit lagi.",
                    'waktu_mulai' => $waktuMulai->format('H:i'),
                    'waktu_buka_absen' => $batasAbsen->format('H:i')
                ], 403);
            }

            // 3. Cek apakah sudah absen
            $existingAbsensi = Absensi::where('sesi_absensi_id', $sesiAbsensi->id)
                ->where('mahasiswa_id', $mahasiswaData['mahasiswa_id'])
                ->first();

            if ($existingAbsensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi untuk sesi ini',
                    'data' => [
                        'waktu_absen' => $existingAbsensi->waktu_absen,
                        'status' => $existingAbsensi->status
                    ]
                ], 409);
            }

            // 4. Upload foto
            $fotoPath = $this->uploadFoto($request->file('foto'), $mahasiswaData['nim']);

            // 5. Tentukan status berdasarkan status sesi
            $waktuAbsen = now();
            $status = 'pending'; // Default pending jika sesi masih draft
            
            // Jika sesi sudah aktif (dosen sudah mulai kelas), langsung hadir
            if ($sesiAbsensi->status === 'aktif') {
                $status = 'hadir';
            }

            // 6. Simpan absensi
            $absensi = Absensi::create([
                'sesi_absensi_id' => $sesiAbsensi->id,
                'mahasiswa_id' => $mahasiswaData['mahasiswa_id'],
                'status' => $status,
                'waktu_absen' => $waktuAbsen,
                'foto_absensi' => $fotoPath,
                'fingerprint_hash' => $mahasiswaData['fingerprint_hash'],
                'device_id' => $device->id,
                'verification_method' => $device->device_type,
                'metode_absen' => 'fingerprint',
                'confidence_score' => $mahasiswaData['confidence_score'],
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
            ]);

            // 7. Update device last seen
            $device->updateLastSeen();

            // 8. Log activity
            LogHelper::create(
                $mahasiswaData['mahasiswa_id'], 
                'Absensi', 
                "Absensi berhasil untuk {$sesiAbsensi->kelas->nama_kelas} - {$sesiAbsensi->mataKuliah->nama_mk}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat',
                'data' => [
                    'mahasiswa' => [
                        'nim' => $mahasiswaData['nim'],
                        'nama' => $mahasiswaData['nama'],
                        'kelas' => $mahasiswaData['kelas'],
                    ],
                    'absensi' => [
                        'id' => $absensi->id,
                        'status' => $absensi->status,
                        'waktu_absen' => $absensi->waktu_absen->format('Y-m-d H:i:s'),
                        'verification_method' => $absensi->verification_method,
                        'confidence_score' => $absensi->confidence_score,
                    ],
                    'sesi' => [
                        'mata_kuliah' => $sesiAbsensi->mataKuliah->nama_mk,
                        'kelas' => $sesiAbsensi->kelas->nama_kelas,
                        'dosen' => $sesiAbsensi->dosen->nama ?? '-',
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Device absensi error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absensi',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Upload foto absensi
     */
    private function uploadFoto($file, $nim)
    {
        $filename = 'absensi_' . $nim . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('absensi/fotos', $filename, 'public');
        
        return $path;
    }

    /**
     * Validasi device berdasarkan header
     */
    private function validateDevice(Request $request)
    {
        $deviceId = $request->header('X-Device-ID');
        $deviceKey = $request->header('X-Device-Key');

        if (!$deviceId || !$deviceKey) {
            return null;
        }

        // Cari device
        $device = Device::where('device_id', $deviceId)
            ->where('status', 'active')
            ->first();

        if (!$device) {
            return null;
        }

        // Validasi device key (dalam implementasi nyata, gunakan hash/encryption)
        // Untuk sementara, device_key bisa disimpan di config device
        $expectedKey = config('devices.api_key', 'default-device-key');
        
        if ($deviceKey !== $expectedKey) {
            return null;
        }

        return $device;
    }

    /**
     * Get sesi absensi aktif untuk device
     * 
     * GET /api/device/sesi-aktif
     */
    public function getSesiAktif(Request $request)
    {
        $device = $this->validateDevice($request);
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak valid'
            ], 401);
        }

        $sesiAktif = SesiAbsensi::where('status', 'aktif')
            ->where('tanggal', now()->format('Y-m-d'))
            ->with(['kelas', 'mataKuliah', 'dosen'])
            ->get()
            ->map(function($sesi) {
                return [
                    'id' => $sesi->id,
                    'kode_absensi' => $sesi->kode_absensi,
                    'mata_kuliah' => $sesi->mataKuliah->nama_mk,
                    'kelas' => $sesi->kelas->nama_kelas,
                    'dosen' => $sesi->dosen->nama ?? '-',
                    'waktu_mulai' => $sesi->waktu_mulai->format('H:i'),
                    'waktu_selesai' => $sesi->waktu_selesai->format('H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sesiAktif
        ]);
    }

    /**
     * Verifikasi kode absensi
     * 
     * POST /api/device/verify-kode
     */
    public function verifyKode(Request $request)
    {
        $device = $this->validateDevice($request);
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak valid'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'kode_absensi' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sesi = SesiAbsensi::where('kode_absensi', $request->kode_absensi)
            ->where('status', 'aktif')
            ->with(['kelas', 'mataKuliah', 'dosen'])
            ->first();

        if (!$sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Kode absensi tidak valid atau sesi sudah ditutup'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode valid',
            'data' => [
                'mata_kuliah' => $sesi->mataKuliah->nama_mk,
                'kelas' => $sesi->kelas->nama_kelas,
                'dosen' => $sesi->dosen->nama ?? '-',
                'waktu_mulai' => $sesi->waktu_mulai->format('H:i'),
                'waktu_selesai' => $sesi->waktu_selesai->format('H:i'),
            ]
        ]);
    }
}
