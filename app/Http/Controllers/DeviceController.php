<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Helpers\LogHelper;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices
     */
    public function index(Request $request)
    {
        $query = Device::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('device_type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $devices = $query->orderBy('created_at', 'desc')->get();

        // Statistics
        $stats = [
            'total' => Device::count(),
            'active' => Device::where('status', 'active')->count(),
            'online' => Device::online()->count(),
            'error' => Device::where('status', 'error')->count()
        ];

        return view('admin.devices.index', compact('devices', 'stats'));
    }

    /**
     * Show the form for creating a new device
     */
    public function create()
    {
        return view('admin.devices.create');
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50|unique:devices,device_id',
            'device_name' => 'required|string|max:100',
            'device_type' => 'required|in:fingerprint,camera,hybrid',
            'model' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'firmware_version' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,maintenance,error',
            'notes' => 'nullable|string'
        ]);

        $device = Device::create($validated);

        LogHelper::create(auth()->id(), 'Manajemen Perangkat', 'Perangkat baru ditambahkan: ' . $device->device_name);

        return redirect()->route('devices.index')->with('success', 'Perangkat berhasil ditambahkan.');
    }

    /**
     * Display the specified device
     */
    public function show($id)
    {
        $device = Device::findOrFail($id);
        
        LogHelper::view(auth()->id(), 'Manajemen Perangkat', 'Melihat detail perangkat: ' . $device->device_name);

        return view('admin.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified device
     */
    public function edit($id)
    {
        $device = Device::findOrFail($id);
        return view('admin.devices.edit', compact('device'));
    }

    /**
     * Update the specified device
     */
    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $validated = $request->validate([
            'device_id' => 'required|string|max:50|unique:devices,device_id,' . $id,
            'device_name' => 'required|string|max:100',
            'device_type' => 'required|in:fingerprint,camera,hybrid',
            'model' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'firmware_version' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,maintenance,error',
            'notes' => 'nullable|string'
        ]);

        $device->update($validated);

        LogHelper::update(auth()->id(), 'Manajemen Perangkat', 'Perangkat diupdate: ' . $device->device_name);

        return redirect()->route('devices.index')->with('success', 'Perangkat berhasil diupdate.');
    }

    /**
     * Remove the specified device
     */
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $name = $device->device_name;
        
        $device->delete();

        LogHelper::delete(auth()->id(), 'Manajemen Perangkat', 'Perangkat dihapus: ' . $name);

        return redirect()->route('devices.index')->with('success', 'Perangkat berhasil dihapus.');
    }

    /**
     * Update device last seen (called by device via API)
     */
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|exists:devices,device_id',
            'ip_address' => 'nullable|ip',
            'firmware_version' => 'nullable|string|max:20'
        ]);

        $device = Device::where('device_id', $validated['device_id'])->first();
        
        $updateData = ['last_seen' => now()];
        
        if (isset($validated['ip_address'])) {
            $updateData['ip_address'] = $validated['ip_address'];
        }
        
        if (isset($validated['firmware_version'])) {
            $updateData['firmware_version'] = $validated['firmware_version'];
        }

        $device->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Sync device configuration
     */
    public function syncConfig(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $validated = $request->validate([
            'config' => 'required|array'
        ]);

        $device->update([
            'config' => $validated['config'],
            'last_sync' => now()
        ]);

        LogHelper::update(auth()->id(), 'Manajemen Perangkat', 'Konfigurasi perangkat disinkronkan: ' . $device->device_name);

        return redirect()->back()->with('success', 'Konfigurasi berhasil disinkronkan.');
    }

    /**
     * Test device connection
     */
    public function testConnection($id)
    {
        $device = Device::findOrFail($id);

        // Simulate connection test (in real implementation, ping the device)
        $isOnline = $device->isOnline();

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'last_seen' => $device->getLastSeenHuman(),
            'status' => $device->getConnectionStatus()
        ]);
    }
}
