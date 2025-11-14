<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogDeviceAccess extends Model
{
    use HasFactory;

    protected $table = 'log_device_access';

    protected $fillable = [
        'device_id',
        'action',
        'endpoint',
        'ip_address',
        'user_agent',
        'response_code',
        'response_message',
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];

    /**
     * Relasi ke Device
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
