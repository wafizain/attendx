<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Device API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk autentikasi device (ESP32, fingerprint sensor, dll)
    |
    */

    // API Key untuk autentikasi device
    // Ganti dengan key yang lebih aman di production
    'api_key' => env('DEVICE_API_KEY', 'absensi-device-key-2025'),

    // Timeout untuk device dianggap offline (dalam menit)
    'offline_timeout' => env('DEVICE_OFFLINE_TIMEOUT', 5),

    // Maximum file size untuk foto absensi (dalam KB)
    'max_photo_size' => env('DEVICE_MAX_PHOTO_SIZE', 2048), // 2MB

    // Allowed image formats
    'allowed_photo_formats' => ['jpg', 'jpeg', 'png'],

    // Fingerprint confidence threshold (minimum score untuk diterima)
    'fingerprint_confidence_threshold' => env('FINGERPRINT_CONFIDENCE_THRESHOLD', 70),

    // Toleransi keterlambatan absensi (dalam menit)
    'late_tolerance' => env('ABSENSI_LATE_TOLERANCE', 15),

    // Enable/disable foto requirement
    'require_photo' => env('DEVICE_REQUIRE_PHOTO', true),

    // Enable/disable geolocation requirement
    'require_geolocation' => env('DEVICE_REQUIRE_GEOLOCATION', false),

];
