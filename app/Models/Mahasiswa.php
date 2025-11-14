<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'id_user',
        'nim',
        'nama',
        'email',
        'no_hp',
        'id_prodi',
        'id_kelas',
        'angkatan',
        'status_akademik',
        'foto_path',
        'fp_enrolled',
        'face_enrolled',
        'last_enrolled_at',
        'alamat',
        'password_plain',
    ];

    protected $casts = [
        'fp_enrolled' => 'boolean',
        'face_enrolled' => 'boolean',
        'last_enrolled_at' => 'datetime',
    ];

    /**
     * Relasi ke User (akun login)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Relasi ke Prodi
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi');
    }

    /**
     * Relasi ke Kelas (current)
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    /**
     * Relasi ke Kelas Members (histori lengkap)
     */
    public function kelasMembers()
    {
        return $this->hasMany(KelasMember::class, 'nim', 'nim');
    }

    /**
     * Relasi ke Biometrik
     */
    public function biometrik()
    {
        return $this->hasMany(MahasiswaBiometrik::class, 'nim', 'nim');
    }

    /**
     * Relasi ke Biometrik Aktif
     */
    public function biometrikAktif()
    {
        return $this->hasMany(MahasiswaBiometrik::class, 'nim', 'nim')
            ->whereNull('revoked_at');
    }

    /**
     * Scope untuk mahasiswa aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_akademik', 'aktif');
    }

    /**
     * Scope untuk mahasiswa cuti
     */
    public function scopeCuti($query)
    {
        return $query->where('status_akademik', 'cuti');
    }

    /**
     * Scope untuk mahasiswa lulus
     */
    public function scopeLulus($query)
    {
        return $query->where('status_akademik', 'lulus');
    }

    /**
     * Scope untuk mahasiswa DO
     */
    public function scopeDO($query)
    {
        return $query->where('status_akademik', 'do');
    }

    /**
     * Scope filter by prodi
     */
    public function scopeProdi($query, $prodiId)
    {
        return $query->where('id_prodi', $prodiId);
    }

    /**
     * Scope filter by angkatan
     */
    public function scopeAngkatan($query, $angkatan)
    {
        return $query->where('angkatan', $angkatan);
    }

    /**
     * Scope filter by kelas
     */
    public function scopeKelas($query, $kelasId)
    {
        return $query->where('id_kelas', $kelasId);
    }

    /**
     * Scope pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nim', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Get status akademik label
     */
    public function getStatusAkademikLabelAttribute()
    {
        $labels = [
            'aktif' => 'Aktif',
            'cuti' => 'Cuti',
            'lulus' => 'Lulus',
            'nonaktif' => 'Nonaktif',
            'do' => 'DO',
        ];

        return $labels[$this->status_akademik] ?? 'Unknown';
    }

    /**
     * Get status akademik badge
     */
    public function getStatusAkademikBadgeAttribute()
    {
        $badges = [
            'aktif' => 'success',
            'cuti' => 'warning',
            'lulus' => 'info',
            'nonaktif' => 'secondary',
            'do' => 'danger',
        ];

        return $badges[$this->status_akademik] ?? 'secondary';
    }

    /**
     * Check if has user account
     */
    public function hasUserAccount()
    {
        return !is_null($this->id_user);
    }

    /**
     * Check if biometric enrolled
     */
    public function isBiometrikEnrolled()
    {
        return $this->fp_enrolled || $this->face_enrolled;
    }

    /**
     * Get biometric status
     */
    public function getBiometrikStatusAttribute()
    {
        $status = [];
        if ($this->fp_enrolled) {
            $status[] = 'Sidik Jari';
        }
        if ($this->face_enrolled) {
            $status[] = 'Wajah';
        }
        
        return empty($status) ? 'Belum Enrol' : implode(' + ', $status);
    }

    /**
     * Get statistik
     */
    public function getStatistikAttribute()
    {
        return [
            'total_kelas' => $this->kelasMembers()->count(),
            'kelas_aktif' => $this->kelasMembers()->aktif()->count(),
            'total_biometrik' => $this->biometrik()->count(),
            'biometrik_aktif' => $this->biometrikAktif()->count(),
        ];
    }
}
