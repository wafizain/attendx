<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'kode',
        'nama',
        'kapasitas',
        'lokasi',
        'status',
        'keterangan',
    ];

    /**
     * Relasi ke jadwal kuliah
     */
    public function jadwalKuliah()
    {
        return $this->hasMany(JadwalKuliah::class, 'id_ruangan');
    }

    /**
     * Relasi ke pertemuan
     */
    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class, 'id_ruangan');
    }

    /**
     * Scope untuk ruangan aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%")
              ->orWhere('lokasi', 'like', "%{$search}%");
        });
    }

    /**
     * Check if ruangan available at specific time
     */
    public function isAvailableAt($hari, $jamMulai, $jamSelesai, $tanggalMulai, $tanggalSelesai, $excludeJadwalId = null)
    {
        $query = JadwalKuliah::where('id_ruangan', $this->id)
            ->where('hari', $hari)
            ->where('status', 'aktif')
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                // Time overlap check: (start1 < end2) AND (start2 < end1)
                $q->where(function($q2) use ($jamMulai, $jamSelesai) {
                    $q2->where('jam_mulai', '<', $jamSelesai)
                       ->where('jam_selesai', '>', $jamMulai);
                });
            })
            ->where(function($q) use ($tanggalMulai, $tanggalSelesai) {
                // Date range overlap check
                $q->where(function($q2) use ($tanggalMulai, $tanggalSelesai) {
                    $q2->where('tanggal_mulai', '<=', $tanggalSelesai)
                       ->where('tanggal_selesai', '>=', $tanggalMulai);
                });
            });

        if ($excludeJadwalId) {
            $query->where('id', '!=', $excludeJadwalId);
        }

        return $query->count() === 0;
    }
}
