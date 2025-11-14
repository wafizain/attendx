<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FingerprintService
{
    /**
     * Verifikasi fingerprint dengan database
     * 
     * @param int $fingerprintId ID fingerprint dari sensor
     * @param string|null $fingerprintHash Hash fingerprint untuk verifikasi tambahan
     * @return array|null Data mahasiswa jika ditemukan
     */
    public function verifyFingerprint(int $fingerprintId, ?string $fingerprintHash = null): ?array
    {
        try {
            // Cari mahasiswa berdasarkan fingerprint_id
            $mahasiswa = DB::table('mahasiswa')
                ->where(function($query) use ($fingerprintId) {
                    $query->where('fingerprint_id_1', $fingerprintId)
                          ->orWhere('fingerprint_id_2', $fingerprintId);
                })
                ->where('fingerprint_registered', true)
                ->where('status', 'aktif')
                ->first();

            if (!$mahasiswa) {
                Log::warning('Fingerprint not found', ['fingerprint_id' => $fingerprintId]);
                return null;
            }

            // Get user data
            $user = User::find($mahasiswa->id_user);
            
            if (!$user || $user->role !== 'mahasiswa') {
                Log::warning('User not found or not mahasiswa', ['user_id' => $mahasiswa->id_user]);
                return null;
            }

            // Hitung confidence score (dalam implementasi nyata, ini dari sensor)
            $confidenceScore = 95.0; // Default high confidence

            return [
                'mahasiswa_id' => $user->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'email' => $mahasiswa->email,
                'kelas' => $mahasiswa->kelas,
                'prodi' => $mahasiswa->prodi,
                'fingerprint_id' => $fingerprintId,
                'fingerprint_hash' => $fingerprintHash,
                'confidence_score' => $confidenceScore,
                'verified_at' => now()
            ];

        } catch (\Exception $e) {
            Log::error('Fingerprint verification error', [
                'error' => $e->getMessage(),
                'fingerprint_id' => $fingerprintId
            ]);
            return null;
        }
    }

    /**
     * Registrasi fingerprint untuk mahasiswa
     * 
     * @param int $mahasiswaId ID mahasiswa (dari tabel mahasiswa)
     * @param int $fingerprintId ID fingerprint dari sensor
     * @param string $fingerprintTemplate Template fingerprint
     * @param int $fingerNumber Nomor jari (1 atau 2)
     * @param int|null $deviceId ID device yang digunakan
     * @return bool
     */
    public function registerFingerprint(
        int $mahasiswaId, 
        int $fingerprintId, 
        string $fingerprintTemplate, 
        int $fingerNumber = 1,
        ?int $deviceId = null
    ): bool {
        try {
            // Cek apakah fingerprint ID sudah digunakan
            $exists = DB::table('mahasiswa')
                ->where('id_mahasiswa', '!=', $mahasiswaId)
                ->where(function($query) use ($fingerprintId) {
                    $query->where('fingerprint_id_1', $fingerprintId)
                          ->orWhere('fingerprint_id_2', $fingerprintId);
                })
                ->exists();

            if ($exists) {
                Log::warning('Fingerprint ID already registered', ['fingerprint_id' => $fingerprintId]);
                return false;
            }

            $updateData = [
                'fingerprint_registered' => true,
                'fingerprint_registered_at' => now(),
            ];

            if ($deviceId) {
                $updateData['registered_device_id'] = $deviceId;
            }

            // Update berdasarkan nomor jari
            if ($fingerNumber === 1) {
                $updateData['fingerprint_id_1'] = $fingerprintId;
                $updateData['fingerprint_template_1'] = $fingerprintTemplate;
            } else {
                $updateData['fingerprint_id_2'] = $fingerprintId;
                $updateData['fingerprint_template_2'] = $fingerprintTemplate;
            }

            DB::table('mahasiswa')
                ->where('id_mahasiswa', $mahasiswaId)
                ->update($updateData);

            Log::info('Fingerprint registered successfully', [
                'mahasiswa_id' => $mahasiswaId,
                'fingerprint_id' => $fingerprintId,
                'finger_number' => $fingerNumber
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Fingerprint registration error', [
                'error' => $e->getMessage(),
                'mahasiswa_id' => $mahasiswaId
            ]);
            return false;
        }
    }

    /**
     * Hapus fingerprint mahasiswa
     * 
     * @param int $mahasiswaId
     * @param int $fingerNumber Nomor jari (1 atau 2, 0 untuk semua)
     * @return bool
     */
    public function deleteFingerprint(int $mahasiswaId, int $fingerNumber = 0): bool
    {
        try {
            $updateData = [];

            if ($fingerNumber === 0 || $fingerNumber === 1) {
                $updateData['fingerprint_id_1'] = null;
                $updateData['fingerprint_template_1'] = null;
            }

            if ($fingerNumber === 0 || $fingerNumber === 2) {
                $updateData['fingerprint_id_2'] = null;
                $updateData['fingerprint_template_2'] = null;
            }

            if ($fingerNumber === 0) {
                $updateData['fingerprint_registered'] = false;
                $updateData['fingerprint_registered_at'] = null;
                $updateData['registered_device_id'] = null;
            }

            DB::table('mahasiswa')
                ->where('id_mahasiswa', $mahasiswaId)
                ->update($updateData);

            Log::info('Fingerprint deleted', [
                'mahasiswa_id' => $mahasiswaId,
                'finger_number' => $fingerNumber
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Fingerprint deletion error', [
                'error' => $e->getMessage(),
                'mahasiswa_id' => $mahasiswaId
            ]);
            return false;
        }
    }

    /**
     * Get next available fingerprint ID
     * 
     * @return int
     */
    public function getNextFingerprintId(): int
    {
        $maxId1 = DB::table('mahasiswa')->max('fingerprint_id_1') ?? 0;
        $maxId2 = DB::table('mahasiswa')->max('fingerprint_id_2') ?? 0;
        
        return max($maxId1, $maxId2) + 1;
    }

    /**
     * Check apakah mahasiswa sudah registrasi fingerprint
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function isRegistered(int $userId): bool
    {
        return DB::table('mahasiswa')
            ->where('id_user', $userId)
            ->where('fingerprint_registered', true)
            ->exists();
    }
}
