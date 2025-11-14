<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kelas';

    protected $fillable = [
        'kode',
        'nama',
        'prodi_id',
        'angkatan',
        'semester_aktif',
        'mata_kuliah_id',
        'dosen_id',
        'wali_dosen_id',
        'nama_kelas',
        'tahun_ajaran',
        'semester',
        'ruangan',
        'kapasitas',
        'status',
        'catatan',
    ];

    protected $casts = [
        'status' => 'boolean',
        'angkatan' => 'integer',
    ];

    /**
     * Relasi ke Prodi
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    /**
     * Relasi ke MataKuliah
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Relasi ke Dosen Pengampu (User)
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    /**
     * Relasi ke Wali Dosen (User)
     */
    public function waliDosen()
    {
        return $this->belongsTo(User::class, 'wali_dosen_id');
    }

    /**
     * Relasi ke Members (via kelas_members dengan histori)
     */
    public function members()
    {
        return $this->hasMany(KelasMember::class, 'id_kelas');
    }

    /**
     * Relasi ke Members Aktif
     */
    public function membersAktif()
    {
        return $this->hasMany(KelasMember::class, 'id_kelas')->aktif();
    }

    /**
     * Relasi ke Mahasiswa (many-to-many) - LEGACY
     * Tetap dipertahankan untuk backward compatibility
     */
    public function mahasiswa()
    {
        return $this->belongsToMany(User::class, 'kelas_mahasiswa', 'kelas_id', 'mahasiswa_id')
                    ->withPivot('tanggal_bergabung', 'status')
                    ->withTimestamps();
    }

    /**
     * Relasi ke Jadwal Kelas
     */
    public function jadwal()
    {
        return $this->hasMany(JadwalKelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Jadwal Kuliah (sistem baru)
     */
    public function jadwalKuliah()
    {
        return $this->hasMany(\App\Models\JadwalKuliah::class, 'id_kelas');
    }

    /**
     * Relasi ke Sesi Absensi
     */
    public function sesiAbsensi()
    {
        return $this->hasMany(SesiAbsensi::class, 'kelas_id');
    }

    /**
     * Scope untuk kelas aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope untuk kelas nonaktif
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
        return $query->where('prodi_id', $prodiId);
    }

    /**
     * Scope filter by angkatan
     */
    public function scopeAngkatan($query, $angkatan)
    {
        return $query->where('angkatan', $angkatan);
    }

    /**
     * Scope pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%")
              ->orWhere('nama_kelas', 'like', "%{$search}%");
        });
    }

    /**
     * Get jumlah mahasiswa aktif
     */
    public function getJumlahMahasiswaAktifAttribute()
    {
        return $this->membersAktif()->count();
    }

    /**
     * Get jumlah total mahasiswa (termasuk yang sudah keluar)
     */
    public function getJumlahTotalMahasiswaAttribute()
    {
        return $this->members()->count();
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
     * Get nama lengkap kelas
     */
    public function getNamaLengkapAttribute()
    {
        return "{$this->kode} - {$this->nama} ({$this->angkatan})";
    }

    /**
     * Check if kelas is full
     */
    public function isFull()
    {
        if (!$this->kapasitas) {
            return false;
        }
        return $this->jumlah_mahasiswa_aktif >= $this->kapasitas;
    }
}
