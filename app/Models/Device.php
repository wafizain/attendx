<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'device_type',
        'model',
        'location',
        'ip_address',
        'mac_address',
        'firmware_version',
        'status',
        'api_key',
        'activated_at',
        'activated_by',
        'last_seen',
        'last_sync',
        'config',
        'notes'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'last_sync' => 'datetime',
        'activated_at' => 'datetime',
        'config' => 'array'
    ];

    /**
     * Check if device is online (last seen within 5 minutes)
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }
        return $this->last_seen->diffInMinutes(now()) <= 5;
    }

    /**
     * Get connection status with color
     */
    public function getConnectionStatus(): array
    {
        if (!$this->last_seen) {
            return ['status' => 'Never Connected', 'color' => 'secondary'];
        }

        $minutes = $this->last_seen->diffInMinutes(now());
        
        if ($minutes <= 5) {
            return ['status' => 'Online', 'color' => 'success'];
        } elseif ($minutes <= 30) {
            return ['status' => 'Recently Active', 'color' => 'info'];
        } elseif ($minutes <= 1440) { // 24 hours
            return ['status' => 'Offline', 'color' => 'warning'];
        } else {
            return ['status' => 'Disconnected', 'color' => 'danger'];
        }
    }

    /**
     * Get last seen human readable
     */
    public function getLastSeenHuman(): string
    {
        if (!$this->last_seen) {
            return 'Never';
        }
        return $this->last_seen->diffForHumans();
    }

    /**
     * Update last seen timestamp
     */
    public function updateLastSeen(): void
    {
        $this->update(['last_seen' => now()]);
    }

    /**
     * Update last sync timestamp
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync' => now()]);
    }

    /**
     * Scope for active devices
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for online devices (last seen within 5 minutes)
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen', '>=', now()->subMinutes(5));
    }

    /**
     * Scope for specific device type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Get device type badge
     */
    public function getTypeBadge(): array
    {
        $badges = [
            'fingerprint' => ['label' => 'Fingerprint', 'color' => 'primary'],
            'camera' => ['label' => 'Camera', 'color' => 'info'],
            'hybrid' => ['label' => 'Hybrid', 'color' => 'success']
        ];

        return $badges[$this->device_type] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    /**
     * Get status badge
     */
    public function getStatusBadge(): array
    {
        $badges = [
            'active' => ['label' => 'Active', 'color' => 'success'],
            'inactive' => ['label' => 'Inactive', 'color' => 'secondary'],
            'maintenance' => ['label' => 'Maintenance', 'color' => 'warning'],
            'error' => ['label' => 'Error', 'color' => 'danger']
        ];

        return $badges[$this->status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }
}
