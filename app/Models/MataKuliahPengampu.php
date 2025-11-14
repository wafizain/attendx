<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliahPengampu extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah_pengampu';

    protected $fillable = [
        'id_mk',
        'dosen_id',
        'peran',
        'bobot_persen',
    ];

    /**
     * Relasi ke Mata Kuliah
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'id_mk');
    }

    /**
     * Relasi ke Dosen (User)
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    /**
     * Get peran badge color
     */
    public function getPeranBadgeAttribute()
    {
        $badges = [
            'Pengampu Utama' => 'primary',
            'Ko-Pengampu' => 'info',
            'Asisten' => 'secondary',
        ];

        return $badges[$this->peran] ?? 'secondary';
    }
}
