<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanRateLimit extends Model
{
    use HasFactory;

    protected $table = 'scan_rate_limit';

    public $timestamps = false;

    protected $fillable = [
        'id_pertemuan',
        'id_mahasiswa',
        'last_scan_at',
        'attempt_count',
    ];

    protected $casts = [
        'last_scan_at' => 'datetime',
    ];

    /**
     * Relasi ke pertemuan
     */
    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'id_pertemuan');
    }

    /**
     * Relasi ke mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }
}
