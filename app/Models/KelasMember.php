<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasMember extends Model
{
    use HasFactory;

    protected $table = 'kelas_members';

    protected $fillable = [
        'id_kelas',
        'nim',
        'tanggal_masuk',
        'tanggal_keluar',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
    ];

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    /**
     * Relasi ke Mahasiswa (User)
     */
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'nim', 'no_induk');
    }

    /**
     * Scope untuk anggota aktif (belum keluar)
     */
    public function scopeAktif($query)
    {
        return $query->whereNull('tanggal_keluar');
    }

    /**
     * Scope untuk anggota yang sudah keluar
     */
    public function scopeKeluar($query)
    {
        return $query->whereNotNull('tanggal_keluar');
    }

    /**
     * Check if member is active
     */
    public function isAktif()
    {
        return is_null($this->tanggal_keluar);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->isAktif() ? 'Aktif' : 'Keluar';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->isAktif() ? 'success' : 'secondary';
    }

    /**
     * Get durasi (lama bergabung)
     */
    public function getDurasiAttribute()
    {
        $end = $this->tanggal_keluar ?? now();
        return $this->tanggal_masuk->diffInDays($end);
    }
}
