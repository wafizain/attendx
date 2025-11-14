<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataKuliah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'id_prodi',
        'kurikulum',
        'kode_mk',
        'nama_mk',
        'sks',
        'jenis',
        'semester_rekomendasi',
        'semester',
        'deskripsi',
        'prasyarat',
        'status',
        'kode_eksternal',
    ];

    protected $casts = [
        'status' => 'boolean',
        'prasyarat' => 'array',
    ];

    /**
     * Relasi ke Prodi
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi');
    }

    /**
     * Relasi ke Pengampu (many-to-many via mata_kuliah_pengampu)
     */
    public function pengampu()
    {
        return $this->hasMany(MataKuliahPengampu::class, 'id_mk');
    }

    /**
     * Relasi ke Dosen Pengampu (direct)
     */
    public function dosenPengampu()
    {
        return $this->belongsToMany(User::class, 'mata_kuliah_pengampu', 'id_mk', 'dosen_id')
            ->withPivot('peran', 'bobot_persen')
            ->withTimestamps();
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'mata_kuliah_id');
    }

    /**
     * Relasi ke Jadwal Kuliah (1 mata kuliah bisa memiliki banyak jadwal)
     */
    public function jadwalKuliah()
    {
        return $this->hasMany(\App\Models\JadwalKuliah::class, 'id_mk');
    }

    /**
     * Scope untuk mata kuliah aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope untuk mata kuliah nonaktif
     */
    public function scopeNonaktif($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope filter by prodi
     */
    public function scopeProdi($query, $prodiId)
    {
        return $query->where('id_prodi', $prodiId);
    }

    /**
     * Scope filter by kurikulum
     */
    public function scopeKurikulum($query, $kurikulum)
    {
        return $query->where('kurikulum', $kurikulum);
    }

    /**
     * Scope filter by jenis
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    /**
     * Scope pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode_mk', 'like', "%{$search}%")
              ->orWhere('nama_mk', 'like', "%{$search}%")
              ->orWhere('kode_eksternal', 'like', "%{$search}%");
        });
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status ? 'success' : 'secondary';
    }

    /**
     * Get jenis badge
     */
    public function getJenisBadgeAttribute()
    {
        $badges = [
            'Teori' => 'primary',
            'Praktikum' => 'success',
            'Teori+Praktikum' => 'info',
        ];

        return $badges[$this->jenis] ?? 'secondary';
    }

    /**
     * Get statistik
     */
    public function getStatistikAttribute()
    {
        return [
            'total_kelas' => $this->kelas()->count(),
            'total_pengampu' => $this->pengampu()->count(),
        ];
    }
}
