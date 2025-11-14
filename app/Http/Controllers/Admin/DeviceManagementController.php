<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;

class DeviceManagementController extends Controller
{
    /**
     * Halaman pairing/registrasi device
     */
    public function pairing()
    {
        $pendingDevices = Device::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('admin.devices.pairing', compact('pendingDevices'));
    }

    /**
     * Approve device pairing
     */
    public function approvePairing($id)
    {
        $device = Device::findOrFail($id);
        
        // Generate API key baru
        $apiKey = Str::random(32);
        
        $device->update([
            'status' => 'active',
            'api_key' => hash('sha256', $apiKey),
            'activated_at' => now(),
            'activated_by' => auth()->id(),
        ]);

        LogHelper::create(auth()->id(), 'device', "Menyetujui pairing device: {$device->device_name}");

        return redirect()->back()->with('success', "Device berhasil di-approve. API Key: {$apiKey}");
    }

    /**
     * Reject device pairing
     */
    public function rejectPairing($id)
    {
        $device = Device::findOrFail($id);
        $deviceName = $device->device_name;
        
        $device->delete();

        LogHelper::delete(auth()->id(), 'device', "Menolak pairing device: {$deviceName}");

        return redirect()->back()->with('success', 'Device pairing ditolak dan dihapus.');
    }

    /**
     * Halaman heartbeat & status monitoring
     */
    public function heartbeat()
    {
        $devices = Device::where('status', 'active')
            ->orderBy('last_seen', 'desc')
            ->get()
            ->map(function($device) {
                $device->is_online = $device->last_seen && $device->last_seen->diffInMinutes(now()) <= 5;
                $device->last_seen_human = $device->last_seen ? $device->last_seen->diffForHumans() : 'Belum pernah';
                return $device;
            });

        return view('admin.devices.heartbeat', compact('devices'));
    }

    /**
     * Reset API key device
     */
    public function resetApiKey($id)
    {
        $device = Device::findOrFail($id);
        
        $newApiKey = Str::random(32);
        $device->update([
            'api_key' => hash('sha256', $newApiKey),
        ]);

        LogHelper::update(auth()->id(), 'device', "Reset API key device: {$device->device_name}");

        return redirect()->back()->with('success', "API Key berhasil direset. New API Key: {$newApiKey}");
    }

    /**
     * Deactivate device
     */
    public function deactivate($id)
    {
        $device = Device::findOrFail($id);
        
        $device->update([
            'status' => 'inactive',
        ]);

        LogHelper::update(auth()->id(), 'device', "Menonaktifkan device: {$device->device_name}");

        return redirect()->back()->with('success', 'Device berhasil dinonaktifkan.');
    }

    /**
     * Reactivate device
     */
    public function reactivate($id)
    {
        $device = Device::findOrFail($id);
        
        $device->update([
            'status' => 'active',
        ]);

        LogHelper::update(auth()->id(), 'device', "Mengaktifkan kembali device: {$device->device_name}");

        return redirect()->back()->with('success', 'Device berhasil diaktifkan kembali.');
    }
}
