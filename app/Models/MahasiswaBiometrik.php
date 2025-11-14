<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBiometrik extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa_biometrik';

    public $timestamps = false;

    protected $fillable = [
        'nim',
        'tipe',
        'ext_ref',
        'template_path',
        'face_embedding_path',
        'quality_score',
        'enrolled_at',
        'revoked_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Relasi ke Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    /**
     * Scope untuk template aktif (belum di-revoke)
     */
    public function scopeAktif($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope untuk template yang sudah di-revoke
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope by tipe
     */
    public function scopeTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Check if active
     */
    public function isAktif()
    {
        return is_null($this->revoked_at);
    }

    /**
     * Get tipe badge
     */
    public function getTipeBadgeAttribute()
    {
        return $this->tipe === 'fingerprint' ? 'primary' : 'success';
    }

    /**
     * Get tipe label
     */
    public function getTipeLabelAttribute()
    {
        return $this->tipe === 'fingerprint' ? 'Sidik Jari' : 'Wajah';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->isAktif() ? 'Aktif' : 'Dicabut';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->isAktif() ? 'success' : 'secondary';
    }
}
