<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiAbsensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_absensi';

    protected $fillable = [
        'kelas_id',
        'jadwal_kelas_id',
        'tanggal',
        'topik',
        'pertemuan_ke',
        'waktu_mulai',
        'waktu_selesai',
        'kode_absensi',
        'metode',
        'latitude',
        'longitude',
        'radius_meter',
        'status',
        'started_at',
        'started_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'started_at' => 'datetime',
    ];

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Jadwal Kelas
     */
    public function jadwalKelas()
    {
        return $this->belongsTo(JadwalKelas::class, 'jadwal_kelas_id');
    }

    /**
     * Relasi ke Absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'sesi_absensi_id');
    }

    /**
     * Generate kode absensi unik
     */
    public static function generateKodeAbsensi()
    {
        do {
            $kode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        } while (self::where('kode_absensi', $kode)->exists());

        return $kode;
    }

    /**
     * Check apakah sesi sedang berlangsung
     */
    public function isBerlangsung()
    {
        $now = now();
        return $this->status === 'aktif' && 
               $now->between($this->waktu_mulai, $this->waktu_selesai);
    }

    /**
     * Get statistik absensi
     */
    public function getStatistikAttribute()
    {
        return [
            'hadir_fingerprint' => $this->absensi()
                ->where('status', 'hadir')
                ->where('verification_method', 'fingerprint')
                ->count(),
            'hadir_manual' => $this->absensi()
                ->where('status', 'hadir')
                ->where('verification_method', 'manual')
                ->count(),
            'hadir' => $this->absensi()->where('status', 'hadir')->count(),
            'izin' => $this->absensi()->where('status', 'izin')->count(),
            'sakit' => $this->absensi()->where('status', 'sakit')->count(),
            'alpha' => $this->absensi()->where('status', 'alpha')->count(),
            'total' => $this->absensi()->count(),
        ];
    }
}
