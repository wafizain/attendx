<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPasswordView extends Model
{
    use HasFactory;

    protected $table = 'log_password_views';

    protected $fillable = [
        'admin_id',
        'dosen_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relasi ke Admin
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi ke Dosen
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
