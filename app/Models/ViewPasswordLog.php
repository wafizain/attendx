<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewPasswordLog extends Model
{
    use HasFactory;

    protected $table = 'view_password_logs';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'dosen_user_id',
        'seen_at',
        'ip',
        'reason',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
    ];

    /**
     * Relasi ke Admin (User)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi ke Dosen (User)
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_user_id');
    }
}
