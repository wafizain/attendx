<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertemuanLog extends Model
{
    use HasFactory;

    protected $table = 'pertemuan_log';

    public $timestamps = false;

    protected $fillable = [
        'id_pertemuan',
        'user_id',
        'action',
        'description',
        'old_data',
        'new_data',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke pertemuan
     */
    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'id_pertemuan');
    }

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get action badge
     */
    public function getActionBadge()
    {
        $badges = [
            'open' => ['label' => 'Dibuka', 'color' => 'success', 'icon' => 'fa-door-open'],
            'close' => ['label' => 'Ditutup', 'color' => 'secondary', 'icon' => 'fa-door-closed'],
            'reschedule' => ['label' => 'Dijadwal Ulang', 'color' => 'warning', 'icon' => 'fa-calendar-alt'],
            'cancel' => ['label' => 'Dibatalkan', 'color' => 'danger', 'icon' => 'fa-times-circle'],
            'update' => ['label' => 'Diperbarui', 'color' => 'info', 'icon' => 'fa-edit'],
        ];

        return $badges[$this->action] ?? ['label' => 'Unknown', 'color' => 'secondary', 'icon' => 'fa-question'];
    }
}
