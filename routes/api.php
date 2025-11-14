<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceAbsensiController;
use App\Http\Controllers\Api\PertemuanApiController;
use App\Http\Controllers\FingerprintRegistrationController;
use App\Http\Controllers\DeviceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Device API Routes (untuk ESP32/perangkat fingerprint & kamera)
|--------------------------------------------------------------------------
*/

Route::prefix('device')->group(function () {
    
    // Absensi dari device (fingerprint + foto)
    Route::post('/absensi', [DeviceAbsensiController::class, 'store']);
    
    // Get sesi absensi yang aktif
    Route::get('/sesi-aktif', [DeviceAbsensiController::class, 'getSesiAktif']);
    
    // Verifikasi kode absensi
    Route::post('/verify-kode', [DeviceAbsensiController::class, 'verifyKode']);
    
    // Registrasi fingerprint dari device
    Route::post('/register-fingerprint', [FingerprintRegistrationController::class, 'registerFromDevice']);
    
    // Get next available fingerprint ID
    Route::get('/next-fingerprint-id', [FingerprintRegistrationController::class, 'getNextId']);
    
    // Device heartbeat (untuk monitoring status device)
    Route::post('/heartbeat', [DeviceController::class, 'heartbeat']);
    
    // === NEW: Pertemuan-based API ===
    // Get current active session for device
    Route::get('/current-session', [PertemuanApiController::class, 'getCurrentSession']);
});

/*
|--------------------------------------------------------------------------
| Scan API Routes (untuk ESP32 - sistem pertemuan)
|--------------------------------------------------------------------------
*/

Route::prefix('scan')->group(function () {
    // Scan fingerprint
    Route::post('/fingerprint', [PertemuanApiController::class, 'scanFingerprint']);
    
    // Scan face (untuk verifikasi wajah)
    Route::post('/face', [PertemuanApiController::class, 'scanFace']);
});

/*
|--------------------------------------------------------------------------
| Pertemuan API Routes (untuk monitoring & stats)
|--------------------------------------------------------------------------
*/

Route::prefix('pertemuan')->group(function () {
    // Get pertemuan statistics
    Route::get('/{id}/stats', [PertemuanApiController::class, 'getStats']);
});

/*
|--------------------------------------------------------------------------
| Fingerprint Registration Routes (untuk mahasiswa via web)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('fingerprint')->group(function () {
    
    // Status registrasi fingerprint mahasiswa
    Route::get('/status', [FingerprintRegistrationController::class, 'status']);
    
    // Hapus fingerprint
    Route::delete('/delete', [FingerprintRegistrationController::class, 'delete']);
});
