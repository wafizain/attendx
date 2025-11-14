<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalKuliah extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kuliah';

    protected $fillable = [
        'id_mk',
        'id_dosen',
        'id_kelas',
        'id_ruangan',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'tanggal_mulai',
        'tanggal_selesai',
        'absen_open_min',
        'absen_close_min',
        'grace_late_min',
        'wajah_wajib',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'wajah_wajib' => 'boolean',
    ];

    /**
     * Relasi ke mata kuliah
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'id_mk');
    }

    /**
     * Relasi ke dosen
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'id_dosen');
    }

    /**
     * Relasi ke kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas')->withTrashed();
    }

    /**
     * Relasi ke ruangan
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan');
    }

    /**
     * Relasi ke mahasiswa (peserta)
     */
    public function mahasiswa()
    {
        return $this->belongsToMany(Mahasiswa::class, 'mahasiswa_jadwal', 'id_jadwal', 'id_mahasiswa')
                    ->withTimestamps()
                    ->withPivot('tanggal_daftar');
    }

    /**
     * Relasi ke pertemuan
     */
    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class, 'id_jadwal');
    }

    /**
     * Scope untuk jadwal aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope filter by dosen
     */
    public function scopeDosen($query, $dosenId)
    {
        return $query->where('id_dosen', $dosenId);
    }

    /**
     * Scope filter by hari
     */
    public function scopeHari($query, $hari)
    {
        return $query->where('hari', $hari);
    }

    /**
     * Get nama hari
     */
    public function getHariNamaAttribute()
    {
        $hariMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $hariMap[$this->hari] ?? 'Unknown';
    }

    /**
     * Check for room conflict
     */
    public static function hasRoomConflict($idRuangan, $hari, $jamMulai, $jamSelesai, $tanggalMulai, $tanggalSelesai, $excludeId = null)
    {
        $query = self::where('id_ruangan', $idRuangan)
            ->where('hari', $hari)
            ->where('status', 'aktif')
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                // Time overlap: (start1 < end2) AND (start2 < end1)
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->where(function($q) use ($tanggalMulai, $tanggalSelesai) {
                // Date range overlap
                $q->where('tanggal_mulai', '<=', $tanggalSelesai)
                  ->where('tanggal_selesai', '>=', $tanggalMulai);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check for lecturer conflict
     */
    public static function hasLecturerConflict($idDosen, $hari, $jamMulai, $jamSelesai, $tanggalMulai, $tanggalSelesai, $excludeId = null)
    {
        $query = self::where('id_dosen', $idDosen)
            ->where('hari', $hari)
            ->where('status', 'aktif')
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->where(function($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->where('tanggal_mulai', '<=', $tanggalSelesai)
                  ->where('tanggal_selesai', '>=', $tanggalMulai);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Generate pertemuan for this jadwal
     */
    public function generatePertemuan($jumlahPertemuan = 14)
    {
        $pertemuanList = [];
        $currentDate = Carbon::parse($this->tanggal_mulai);
        $endDate = Carbon::parse($this->tanggal_selesai);
        $mingguKe = 1;

        // Find first occurrence of the target day
        while ($currentDate->dayOfWeek !== $this->hari && $currentDate->lte($endDate)) {
            $currentDate->addDay();
        }

        // Generate pertemuan
        while ($mingguKe <= $jumlahPertemuan && $currentDate->lte($endDate)) {
            $pertemuanList[] = [
                'id_jadwal' => $this->id,
                'minggu_ke' => $mingguKe,
                'tanggal' => $currentDate->format('Y-m-d'),
                'jam_mulai' => $this->jam_mulai,
                'jam_selesai' => $this->jam_selesai,
                'id_ruangan' => $this->id_ruangan,
                'status_sesi' => 'direncanakan',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentDate->addWeek();
            $mingguKe++;
        }

        if (!empty($pertemuanList)) {
            Pertemuan::insert($pertemuanList);
        }

        return count($pertemuanList);
    }

}
