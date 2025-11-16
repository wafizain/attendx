<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semesters';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_pertemuan',
        'pertemuan_uts',
        'pertemuan_uas'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_pertemuan' => 'integer',
        'pertemuan_uts' => 'integer',
        'pertemuan_uas' => 'integer'
    ];

    public function jadwalKuliah()
    {
        return $this->hasMany(JadwalKuliah::class, 'semester_id');
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class, 'semester_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeOrderByLatest($query)
    {
        return $query->orderBy('tahun_ajaran', 'desc')->orderBy('semester', 'desc');
    }

    public function getNamaAttribute()
    {
        $semesterName = $this->semester == 1 ? 'Ganjil' : 'Genap';
        return $this->tahun_ajaran . ' - Semester ' . $semesterName;
    }

    public function getSemesterNameAttribute()
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }

    /**
     * Check if a meeting number is UTS
     */
    public function isUTS($pertemuanKe)
    {
        return $this->pertemuan_uts && $pertemuanKe == $this->pertemuan_uts;
    }

    /**
     * Check if a meeting number is UAS
     */
    public function isUAS($pertemuanKe)
    {
        return $this->pertemuan_uas && $pertemuanKe == $this->pertemuan_uas;
    }

    /**
     * Get meeting type label
     */
    public function getMeetingType($pertemuanKe)
    {
        if ($this->isUTS($pertemuanKe)) {
            return 'UTS';
        }
        if ($this->isUAS($pertemuanKe)) {
            return 'UAS';
        }
        return 'Reguler';
    }
}
