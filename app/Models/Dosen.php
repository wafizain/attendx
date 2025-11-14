<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'dosen';
    public $timestamps = true;
    
    protected $fillable = [
        'id_user',
        'nidn',
        'nip',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'pendidikan_terakhir',
        'email_kampus',
        'no_hp',
        'bidang_keahlian',
        'foto_path',
        'ttd_path',
        'status_pegawai',
        'status_aktif',
        'alamat',
    ];

    protected $casts = [
        'bidang_keahlian' => 'array',
        'status_aktif' => 'boolean',
    ];

    /**
     * Relasi ke User (akun login)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Relasi ke Mata Kuliah yang diampu
     */
    public function mataKuliahDiampu()
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_pengampu', 'dosen_id', 'id_mk')
            ->withPivot('peran', 'bobot_persen')
            ->withTimestamps();
    }

    /**
     * Relasi ke Jadwal Kuliah
     */
    public function jadwalKuliah()
    {
        return $this->hasMany(JadwalKuliah::class, 'id_dosen', 'id_user');
    }

    /**
     * Relasi ke Kelas sebagai pengampu
     */
    public function kelasAsDosenPengampu()
    {
        return $this->hasMany(Kelas::class, 'dosen_id', 'id_user');
    }

    /**
     * Relasi ke Kelas sebagai wali
     */
    public function kelasAsWaliDosen()
    {
        return $this->hasMany(Kelas::class, 'wali_dosen_id', 'id_user');
    }

    /**
     * Scope untuk dosen aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', 1);
    }

    /**
     * Scope untuk dosen nonaktif
     */
    public function scopeNonaktif($query)
    {
        return $query->where('status_aktif', 0);
    }

    /**
     * Scope pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nidn', 'like', "%{$search}%")
              ->orWhere('nip', 'like', "%{$search}%")
              ->orWhere('email_kampus', 'like', "%{$search}%");
        });
    }

    /**
     * Get nama lengkap dengan gelar
     */
    public function getNamaLengkapAttribute()
    {
        $nama = $this->nama;
        if ($this->gelar_depan) {
            $nama = $this->gelar_depan . ' ' . $nama;
        }
        if ($this->gelar_belakang) {
            $nama = $nama . ', ' . $this->gelar_belakang;
        }
        return $nama;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status_aktif ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status_aktif ? 'success' : 'secondary';
    }

    /**
     * Get status pegawai badge
     */
    public function getStatusPegawaiBadgeAttribute()
    {
        $badges = [
            'Tetap' => 'success',
            'Kontrak' => 'warning',
            'LB' => 'info',
        ];

        return $badges[$this->status_pegawai] ?? 'secondary';
    }
}
