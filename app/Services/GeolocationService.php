<?php

namespace App\Services;

class GeolocationService
{
    /**
     * Calculate distance between two coordinates (Haversine formula)
     * Returns distance in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validate if location is within allowed radius
     */
    public function validateLocation($userLat, $userLon, $targetLat, $targetLon, $maxRadius = 100)
    {
        $distance = $this->calculateDistance($userLat, $userLon, $targetLat, $targetLon);

        return [
            'valid' => $distance <= $maxRadius,
            'distance' => round($distance, 2),
            'max_radius' => $maxRadius,
        ];
    }

    /**
     * Validate absensi location
     */
    public function validateAbsensiLocation($pertemuanId, $userLat, $userLon)
    {
        $pertemuan = \App\Models\Pertemuan::find($pertemuanId);
        
        if (!$pertemuan) {
            return [
                'valid' => false,
                'message' => 'Pertemuan tidak ditemukan',
            ];
        }

        $ruangan = $pertemuan->ruangan;
        
        // Check if ruangan has coordinates
        if (!$ruangan->latitude || !$ruangan->longitude) {
            // Skip validation if no coordinates set
            return [
                'valid' => true,
                'message' => 'Validasi lokasi dilewati (koordinat ruangan belum diset)',
            ];
        }

        $maxRadius = config('absensi.geolocation_radius', 100); // meters
        
        $validation = $this->validateLocation(
            $userLat,
            $userLon,
            $ruangan->latitude,
            $ruangan->longitude,
            $maxRadius
        );

        if (!$validation['valid']) {
            return [
                'valid' => false,
                'message' => "Anda terlalu jauh dari lokasi ({$validation['distance']}m). Maksimal {$maxRadius}m",
                'distance' => $validation['distance'],
            ];
        }

        return [
            'valid' => true,
            'message' => 'Lokasi valid',
            'distance' => $validation['distance'],
        ];
    }

    /**
     * Get location from IP address (fallback)
     */
    public function getLocationFromIP($ipAddress)
    {
        try {
            // Using ip-api.com (free tier)
            $response = \Http::get("http://ip-api.com/json/{$ipAddress}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'city' => $data['city'] ?? null,
                    'country' => $data['country'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Geolocation from IP failed: ' . $e->getMessage());
        }

        return null;
    }
}
