<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Prodi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'program_studi';

    protected $fillable = [
        'kode',
        'nama',
        'jenjang',
        'fakultas_id',
        'fakultas',
        'akreditasi',
        'kaprodi_user_id',
        'status',
        'slug',
        'deskripsi',
        'kode_eksternal',
        'email_kontak',
        'telepon_kontak',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Boot model - auto generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prodi) {
            if (empty($prodi->slug)) {
                $prodi->slug = Str::slug($prodi->nama);
            }
        });

        static::updating(function ($prodi) {
            if ($prodi->isDirty('nama') && empty($prodi->slug)) {
                $prodi->slug = Str::slug($prodi->nama);
            }
        });
    }

    /**
     * Relasi ke Kaprodi (User/Dosen)
     */
    public function kaprodi()
    {
        return $this->belongsTo(User::class, 'kaprodi_user_id');
    }

    /**
     * Relasi ke Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'id_prodi');
    }

    /**
     * Relasi ke Mahasiswa Aktif
     */
    public function mahasiswaAktif()
    {
        return $this->hasMany(Mahasiswa::class, 'id_prodi')->where('status_akademik', 'aktif');
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'prodi_id');
    }

    /**
     * Relasi ke Mata Kuliah
     */
    public function mataKuliah()
    {
        return $this->hasMany(MataKuliah::class, 'id_prodi');
    }

    /**
     * Relasi ke Dosen yang mengajar di prodi ini melalui mata kuliah
     */
    public function dosen()
    {
        return $this->hasManyThrough(
            User::class,
            MataKuliahPengampu::class,
            'id_mk',
            'id',
            'id',
            'dosen_id'
        )->join('mata_kuliah', 'mata_kuliah_pengampu.id_mk', '=', 'mata_kuliah.id')
         ->where('mata_kuliah.id_prodi', $this->id)
         ->where('users.role', 'dosen')
         ->distinct();
    }

    /**
     * Relasi ke Dosen melalui kelas (alternatif)
     */
    public function dosenKelas()
    {
        return $this->hasManyThrough(
            User::class,
            Kelas::class,
            'prodi_id',
            'id',
            'id',
            'dosen_id'
        )->where('users.role', 'dosen')->distinct();
    }

    /**
     * Scope untuk prodi aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope untuk prodi nonaktif
     */
    public function scopeNonaktif($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope untuk filter by jenjang
     */
    public function scopeJenjang($query, $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%");
        });
    }

    /**
     * Get statistik prodi
     */
    public function getStatistikAttribute()
    {
        return [
            'total_mahasiswa' => $this->mahasiswa()->count(),
            'mahasiswa_aktif' => $this->mahasiswaAktif()->count(),
            'total_kelas' => $this->kelas()->count(),
            'kelas_aktif' => $this->kelas()->where('status', 1)->count(),
            'total_mata_kuliah' => $this->mataKuliah()->count(),
            'total_dosen' => $this->dosenKelas()->count(),
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status ? 'success' : 'secondary';
    }
}
