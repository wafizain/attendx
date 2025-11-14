<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'id_pertemuan',
        'id_mahasiswa',
        'sesi_absensi_id',
        'mahasiswa_id',
        'status',
        'waktu_scan',
        'waktu_absen',
        'foto_path',
        'foto_absensi',
        'fingerprint_hash',
        'device_id',
        'confidence',
        'confidence_score',
        'verified_by',
        'verification_method',
        'metode_absen',
        'latitude',
        'longitude',
        'ip_address',
        'device_info',
        'keterangan',
        'bukti_file',
    ];

    protected $casts = [
        'waktu_scan' => 'datetime',
        'waktu_absen' => 'datetime',
        'confidence' => 'decimal:2',
    ];

    /**
     * Relasi ke Sesi Absensi
     */
    public function sesiAbsensi()
    {
        return $this->belongsTo(SesiAbsensi::class, 'sesi_absensi_id');
    }

    /**
     * Relasi ke Mahasiswa (User)
     */
    public function mahasiswa()
    {
        // Support both id_mahasiswa and mahasiswa_id
        $foreignKey = Schema::hasColumn('absensi', 'id_mahasiswa') ? 'id_mahasiswa' : 'mahasiswa_id';
        return $this->belongsTo(User::class, $foreignKey);
    }

    /**
     * Relasi ke Pertemuan
     */
    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'id_pertemuan');
    }

    /**
     * Relasi ke Device
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    /**
     * Check apakah terlambat
     */
    public function isTerlambat()
    {
        if (!$this->waktu_absen || !$this->sesiAbsensi) {
            return false;
        }

        return $this->waktu_absen->gt($this->sesiAbsensi->waktu_mulai);
    }

    /**
     * Get durasi keterlambatan dalam menit
     */
    public function getDurasiTerlambatAttribute()
    {
        if (!$this->isTerlambat()) {
            return 0;
        }

        return $this->waktu_absen->diffInMinutes($this->sesiAbsensi->waktu_mulai);
    }

    /**
     * Check apakah absensi menggunakan fingerprint
     */
    public function isFingerprint(): bool
    {
        return in_array($this->verification_method, ['fingerprint', 'hybrid']);
    }

    /**
     * Check apakah absensi menggunakan kamera
     */
    public function isCamera(): bool
    {
        return in_array($this->verification_method, ['camera', 'hybrid']);
    }

    /**
     * Get verification method badge
     */
    public function getVerificationBadge(): array
    {
        $badges = [
            'fingerprint' => ['label' => 'Sidik Jari', 'color' => 'primary', 'icon' => 'fa-fingerprint'],
            'camera' => ['label' => 'Kamera', 'color' => 'info', 'icon' => 'fa-camera'],
            'hybrid' => ['label' => 'Hybrid', 'color' => 'success', 'icon' => 'fa-check-double'],
            'manual' => ['label' => 'Manual', 'color' => 'secondary', 'icon' => 'fa-hand-paper']
        ];

        return $badges[$this->verification_method] ?? ['label' => 'Unknown', 'color' => 'secondary', 'icon' => 'fa-question'];
    }

    /**
     * Get foto absensi URL
     */
    public function getFotoUrl(): ?string
    {
        $foto = $this->foto_path ?? $this->foto_absensi;
        if (!$foto) {
            return null;
        }

        return asset('storage/' . $foto);
    }

    /**
     * Check rate limit for scanning
     */
    public static function checkRateLimit($idPertemuan, $idMahasiswa, $limitSeconds = 30)
    {
        $lastScan = ScanRateLimit::where('id_pertemuan', $idPertemuan)
            ->where('id_mahasiswa', $idMahasiswa)
            ->first();

        if (!$lastScan) {
            return true; // No previous scan, allow
        }

        $secondsSinceLastScan = now()->diffInSeconds($lastScan->last_scan_at);
        
        if ($secondsSinceLastScan < $limitSeconds) {
            return false; // Too soon, reject
        }

        return true;
    }

    /**
     * Update rate limit
     */
    public static function updateRateLimit($idPertemuan, $idMahasiswa)
    {
        ScanRateLimit::updateOrCreate(
            [
                'id_pertemuan' => $idPertemuan,
                'id_mahasiswa' => $idMahasiswa,
            ],
            [
                'last_scan_at' => now(),
                'attempt_count' => \DB::raw('attempt_count + 1'),
            ]
        );
    }

    /**
     * Validate mahasiswa is participant
     */
    public static function validatePeserta($idPertemuan, $idMahasiswa)
    {
        $pertemuan = Pertemuan::find($idPertemuan);
        if (!$pertemuan) {
            return false;
        }

        return $pertemuan->jadwal->mahasiswa()->where('mahasiswa.id', $idMahasiswa)->exists();
    }

    /**
     * Validate device location
     */
    public static function validateDeviceLocation($deviceId, $idRuangan)
    {
        return \DB::table('device_ruangan')
            ->where('device_id', $deviceId)
            ->where('id_ruangan', $idRuangan)
            ->exists();
    }

    /**
     * Create or update absensi (upsert pattern)
     */
    public static function recordAbsensi($data)
    {
        return self::updateOrCreate(
            [
                'id_pertemuan' => $data['id_pertemuan'],
                'id_mahasiswa' => $data['id_mahasiswa'],
            ],
            $data
        );
    }
}
