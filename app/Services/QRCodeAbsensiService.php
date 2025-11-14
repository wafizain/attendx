<?php

namespace App\Services;

use App\Models\Pertemuan;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class QRCodeAbsensiService
{
    /**
     * Generate QR Code for pertemuan
     * Install: composer require simplesoftwareio/simple-qrcode
     */
    public function generateQRCode(Pertemuan $pertemuan)
    {
        // Generate unique token
        $token = Str::random(32);
        
        // Store token in cache (valid for window duration)
        $window = $pertemuan->getAbsensiWindow();
        $expiresAt = $window['close'];
        
        Cache::put("qr_pertemuan_{$pertemuan->id}", $token, $expiresAt);
        
        // Generate QR data
        $qrData = [
            'pertemuan_id' => $pertemuan->id,
            'token' => $token,
            'expires_at' => $expiresAt->timestamp,
        ];
        
        $qrString = json_encode($qrData);
        
        // Generate QR Code image
        // return \QrCode::size(300)->generate($qrString);
        
        return [
            'qr_data' => $qrString,
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verify QR Code scan
     */
    public function verifyQRCode($pertemuanId, $token, $mahasiswaId)
    {
        // Check if token valid
        $cachedToken = Cache::get("qr_pertemuan_{$pertemuanId}");
        
        if (!$cachedToken || $cachedToken !== $token) {
            return [
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa',
            ];
        }

        // Get pertemuan
        $pertemuan = Pertemuan::find($pertemuanId);
        
        if (!$pertemuan) {
            return [
                'success' => false,
                'message' => 'Pertemuan tidak ditemukan',
            ];
        }

        // Check if pertemuan is active
        if ($pertemuan->status_sesi !== 'berjalan') {
            return [
                'success' => false,
                'message' => 'Pertemuan belum dibuka atau sudah ditutup',
            ];
        }

        // Validate mahasiswa
        $mahasiswa = Mahasiswa::find($mahasiswaId);
        
        if (!$mahasiswa) {
            return [
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan',
            ];
        }

        // Check if mahasiswa is participant
        if (!\App\Models\Absensi::validatePeserta($pertemuanId, $mahasiswaId)) {
            return [
                'success' => false,
                'message' => 'Anda bukan peserta mata kuliah ini',
            ];
        }

        // Check rate limit
        if (!\App\Models\Absensi::checkRateLimit($pertemuanId, $mahasiswaId)) {
            return [
                'success' => false,
                'message' => 'Tunggu 30 detik sebelum scan lagi',
            ];
        }

        // Determine status
        $status = $pertemuan->determineStatus();

        // Record absensi
        $absensi = \App\Models\Absensi::recordAbsensi([
            'id_pertemuan' => $pertemuanId,
            'id_mahasiswa' => $mahasiswaId,
            'waktu_scan' => now(),
            'status' => $status,
            'verified_by' => 'manual', // QR Code
            'device_id' => 'QR_CODE',
        ]);

        // Update rate limit
        \App\Models\Absensi::updateRateLimit($pertemuanId, $mahasiswaId);

        return [
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'data' => [
                'status' => $status,
                'waktu_scan' => now()->format('H:i:s'),
            ],
        ];
    }

    /**
     * Get QR Code URL for display
     */
    public function getQRCodeUrl(Pertemuan $pertemuan)
    {
        $qrData = $this->generateQRCode($pertemuan);
        
        // Generate URL that mahasiswa can scan
        $url = route('qr.scan', [
            'pertemuan_id' => $pertemuan->id,
            'token' => $qrData['token'],
        ]);
        
        return $url;
    }
}
