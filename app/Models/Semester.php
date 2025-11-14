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
        'tanggal_selesai'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date'
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
        return $this->tahun_ajaran . ' - Semester ' . $this->semester;
    }
}
