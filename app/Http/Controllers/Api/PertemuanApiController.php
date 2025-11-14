<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Models\Absensi;
use App\Models\Device;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PertemuanApiController extends Controller
{
    /**
     * Get current active session for device
     * 
     * GET /api/device/current-session?device_id=xxx
     */
    public function getCurrentSession(Request $request)
    {
        $deviceId = $request->query('device_id');
        
        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'device_id required',
            ], 400);
        }

        // Validate device
        $device = Device::where('device_id', $deviceId)
            ->where('status', 'active')
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found or inactive',
            ], 404);
        }

        // Update device last seen
        $device->updateLastSeen();

        // Get ruangan from device mapping
        $deviceRuangan = \DB::table('device_ruangan')
            ->where('device_id', $device->id)
            ->first();

        if (!$deviceRuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Device not mapped to any room',
            ], 404);
        }

        // Find active pertemuan in this room
        $now = Carbon::now();
        $pertemuan = Pertemuan::with(['jadwal.mataKuliah', 'jadwal.dosen', 'ruangan'])
            ->where('id_ruangan', $deviceRuangan->id_ruangan)
            ->where('status_sesi', 'berjalan')
            ->whereDate('tanggal', $now->toDateString())
            ->first();

        // If no active session, check for auto-open window
        if (!$pertemuan) {
            $pertemuan = Pertemuan::with(['jadwal.mataKuliah', 'jadwal.dosen', 'ruangan'])
                ->where('id_ruangan', $deviceRuangan->id_ruangan)
                ->where('status_sesi', 'direncanakan')
                ->whereDate('tanggal', $now->toDateString())
                ->get()
                ->first(function($p) use ($now) {
                    $window = $p->getAbsensiWindow();
                    return $now->gte($window['open']) && $now->lte($window['close']);
                });

            if ($pertemuan) {
                // Auto-open
                $pertemuan->checkAutoStatus();
                $pertemuan->refresh();
            }
        }

        if (!$pertemuan) {
            return response()->json([
                'success' => false,
                'message' => 'No active session in this room',
            ], 404);
        }

        $window = $pertemuan->getAbsensiWindow();

        return response()->json([
            'success' => true,
            'data' => [
                'pertemuan_id' => $pertemuan->id,
                'mata_kuliah' => $pertemuan->jadwal->mataKuliah->nama_mk,
                'kode_mk' => $pertemuan->jadwal->mataKuliah->kode_mk,
                'dosen' => $pertemuan->jadwal->dosen->name,
                'ruangan' => $pertemuan->ruangan->nama_ruangan,
                'tanggal' => $pertemuan->tanggal->format('Y-m-d'),
                'jam_mulai' => $pertemuan->jam_mulai,
                'jam_selesai' => $pertemuan->jam_selesai,
                'minggu_ke' => $pertemuan->minggu_ke,
                'status_sesi' => $pertemuan->status_sesi,
                'wajah_wajib' => (bool) $pertemuan->jadwal->wajah_wajib,
                'window' => [
                    'open' => $window['open']->format('Y-m-d H:i:s'),
                    'late' => $window['late']->format('Y-m-d H:i:s'),
                    'close' => $window['close']->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }

    /**
     * Scan fingerprint
     * 
     * POST /api/scan/fingerprint
     * {
     *   "device_id": "ESP32_001",
     *   "pertemuan_id": 123,
     *   "nim": "2021001",
     *   "fingerprint_id": 5,
     *   "confidence": 95.5,
     *   "timestamp": "2025-11-11 08:15:30"
     * }
     */
    public function scanFingerprint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'nim' => 'required|string',
            'fingerprint_id' => 'nullable|integer',
            'confidence' => 'nullable|numeric|min:0|max:100',
            'timestamp' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // 1. Validate device
            $device = Device::where('device_id', $request->device_id)
                ->where('status', 'active')
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or inactive',
                ], 404);
            }

            // 2. Get pertemuan
            $pertemuan = Pertemuan::with('jadwal')->findOrFail($request->pertemuan_id);

            // 3. Validate room match
            if (!Absensi::validateDeviceLocation($device->id, $pertemuan->id_ruangan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not authorized for this room',
                ], 403);
            }

            // 4. Find mahasiswa
            $mahasiswa = Mahasiswa::where('nim', $request->nim)->first();
            
            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found',
                ], 404);
            }

            // 5. Validate mahasiswa is participant
            if (!Absensi::validatePeserta($pertemuan->id, $mahasiswa->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not a participant of this class',
                ], 403);
            }

            // 6. Check rate limit
            if (!Absensi::checkRateLimit($pertemuan->id, $mahasiswa->id, 30)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait 30 seconds before scanning again',
                ], 429);
            }

            // 7. Validate window
            $scanTime = $request->timestamp ? Carbon::parse($request->timestamp) : Carbon::now();
            
            // Allow ±2 minutes clock skew
            $scanTime = $this->adjustClockSkew($scanTime);
            
            $window = $pertemuan->getAbsensiWindow();
            
            if ($scanTime->lt($window['open'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance not yet open',
                    'open_at' => $window['open']->format('H:i'),
                ], 403);
            }

            if ($scanTime->gt($window['close'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already closed',
                    'closed_at' => $window['close']->format('H:i'),
                ], 403);
            }

            // 8. Determine status
            $status = $pertemuan->determineStatus($scanTime);
            
            if (in_array($status, ['terlalu_awal', 'terlambat_tutup'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scan outside valid window',
                ], 403);
            }

            // 9. Check if face required
            $requireFace = (bool) $pertemuan->jadwal->wajah_wajib;
            
            if ($requireFace) {
                $status = 'pending_face';
            }

            // 10. Record absensi
            $absensi = Absensi::recordAbsensi([
                'id_pertemuan' => $pertemuan->id,
                'id_mahasiswa' => $mahasiswa->id,
                'waktu_scan' => $scanTime,
                'status' => $status,
                'device_id' => $device->device_id,
                'confidence' => $request->confidence,
                'verified_by' => 'device',
            ]);

            // 11. Update rate limit
            Absensi::updateRateLimit($pertemuan->id, $mahasiswa->id);

            // 12. Update device last seen
            $device->updateLastSeen();

            $response = [
                'success' => true,
                'message' => 'Scan successful',
                'data' => [
                    'absensi_id' => $absensi->id,
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'status' => $status,
                    'waktu_scan' => $scanTime->format('H:i:s'),
                ],
            ];

            // If face required, add token
            if ($requireFace) {
                $token = $this->generateFaceToken($absensi->id);
                $response['require_face'] = true;
                $response['face_token'] = $token;
                $response['message'] = 'Face verification required';
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            \Log::error('Scan fingerprint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Scan face (for face verification)
     * 
     * POST /api/scan/face
     * {
     *   "device_id": "ESP32_001",
     *   "pertemuan_id": 123,
     *   "nim": "2021001",
     *   "face_token": "xxx",
     *   "image": "base64_encoded_image",
     *   "confidence": 98.5
     * }
     */
    public function scanFace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'nim' => 'required|string',
            'face_token' => 'nullable|string',
            'image' => 'required|string', // base64
            'confidence' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // 1. Validate device
            $device = Device::where('device_id', $request->device_id)
                ->where('status', 'active')
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found',
                ], 404);
            }

            // 2. Find mahasiswa
            $mahasiswa = Mahasiswa::where('nim', $request->nim)->first();
            
            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found',
                ], 404);
            }

            // 3. Find pending absensi
            $absensi = Absensi::where('id_pertemuan', $request->pertemuan_id)
                ->where('id_mahasiswa', $mahasiswa->id)
                ->where('status', 'pending_face')
                ->first();

            if (!$absensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending face verification found',
                ], 404);
            }

            // 4. Check timeout (2 minutes)
            if ($absensi->waktu_scan->diffInMinutes(now()) > 2) {
                $absensi->update(['verified_by' => 'system', 'keterangan' => 'Face verification timeout']);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Face verification timeout',
                ], 408);
            }

            // 5. Save face image
            $fotoPath = $this->saveFaceImage($request->image, $mahasiswa->nim);

            // 6. Determine final status based on scan time
            $pertemuan = Pertemuan::find($request->pertemuan_id);
            $finalStatus = $pertemuan->determineStatus($absensi->waktu_scan);

            // 7. Update absensi
            $absensi->update([
                'status' => $finalStatus,
                'foto_path' => $fotoPath,
                'confidence' => $request->confidence ?? $absensi->confidence,
                'verified_by' => 'device',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face verification successful',
                'data' => [
                    'absensi_id' => $absensi->id,
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'status' => $finalStatus,
                    'waktu_scan' => $absensi->waktu_scan->format('H:i:s'),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Scan face error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Adjust clock skew (±2 minutes tolerance)
     */
    private function adjustClockSkew($timestamp)
    {
        $serverTime = Carbon::now();
        $diff = $serverTime->diffInMinutes($timestamp, false);
        
        // If difference is within ±2 minutes, use server time
        if (abs($diff) <= 2) {
            return $serverTime;
        }
        
        return $timestamp;
    }

    /**
     * Generate face verification token
     */
    private function generateFaceToken($absensiId)
    {
        return base64_encode($absensiId . '|' . time() . '|' . Str::random(16));
    }

    /**
     * Save face image from base64
     */
    private function saveFaceImage($base64Image, $nim)
    {
        // Remove data:image/xxx;base64, prefix if exists
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $image = base64_decode($image);
        
        $filename = 'face_' . $nim . '_' . time() . '_' . Str::random(8) . '.jpg';
        $path = 'absensi/faces/' . $filename;
        
        Storage::disk('public')->put($path, $image);
        
        return $path;
    }

    /**
     * Get pertemuan statistics (for monitoring)
     * 
     * GET /api/pertemuan/{id}/stats
     */
    public function getStats($id)
    {
        $pertemuan = Pertemuan::with(['jadwal', 'absensi'])->findOrFail($id);
        
        $stats = $pertemuan->getStatistikKehadiran();
        $window = $pertemuan->getAbsensiWindow();

        return response()->json([
            'success' => true,
            'data' => [
                'pertemuan_id' => $pertemuan->id,
                'status_sesi' => $pertemuan->status_sesi,
                'statistik' => $stats,
                'window' => [
                    'open' => $window['open']->format('H:i'),
                    'late' => $window['late']->format('H:i'),
                    'close' => $window['close']->format('H:i'),
                ],
                'is_open' => $pertemuan->isAbsensiOpen(),
            ],
        ]);
    }
}
