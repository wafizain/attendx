<?php

namespace App\Helpers;

use App\Models\Log;

class LogHelper
{
    /**
     * Catat log aktivitas
     * 
     * @param string $action - Jenis aksi (login, logout, create, update, delete, view, dll)
     * @param string|null $module - Module terkait (admin, dosen, mahasiswa, absensi, dll)
     * @param string|null $description - Deskripsi detail aktivitas
     * @param array|null $data - Data tambahan dalam format array
     * @return Log
     */
    public static function record($action, $module = null, $description = null, $data = null)
    {
        return Log::record($action, $module, $description, $data);
    }

    /**
     * Log untuk aktivitas login
     */
    public static function login($userId = null, $description = null)
    {
        return Log::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => 'login',
            'module' => 'auth',
            'description' => $description ?? 'User berhasil login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log untuk aktivitas logout
     */
    public static function logout($userId = null, $description = null)
    {
        return Log::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => 'logout',
            'module' => 'auth',
            'description' => $description ?? 'User berhasil logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log untuk aktivitas create
     */
    public static function create($module, $description, $data = null)
    {
        return self::record('create', $module, $description, $data);
    }

    /**
     * Log untuk aktivitas update
     */
    public static function update($module, $description, $data = null)
    {
        return self::record('update', $module, $description, $data);
    }

    /**
     * Log untuk aktivitas delete
     */
    public static function delete($module, $description, $data = null)
    {
        return self::record('delete', $module, $description, $data);
    }

    /**
     * Log untuk aktivitas view
     */
    public static function view($module, $description, $data = null)
    {
        return self::record('view', $module, $description, $data);
    }

    /**
     * Log general dengan format: action, table, record_id, description
     * Digunakan untuk kompatibilitas dengan controller lama
     */
    public static function log($action, $table, $recordId = null, $description = null)
    {
        return Log::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $table,
            'description' => $description ?? "{$action} pada {$table}",
            'data' => $recordId ? json_encode(['record_id' => $recordId]) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
