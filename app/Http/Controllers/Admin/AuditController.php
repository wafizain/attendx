<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\LogPasswordView;
use App\Models\LogDeviceAccess;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Log aktivitas umum
     */
    public function activity(Request $request)
    {
        $query = LogAktivitas::with('user')->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->paginate(50);

        return view('admin.audit.activity', compact('logs'));
    }

    /**
     * Log lihat password dosen
     */
    public function password(Request $request)
    {
        $query = LogPasswordView::with(['admin', 'dosen'])->orderBy('created_at', 'desc');

        // Filter by admin
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by dosen
        if ($request->filled('dosen_id')) {
            $query->where('dosen_id', $request->dosen_id);
        }

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->paginate(50);

        return view('admin.audit.password', compact('logs'));
    }

    /**
     * Log akses device
     */
    public function device(Request $request)
    {
        $query = LogDeviceAccess::with('device')->orderBy('created_at', 'desc');

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->paginate(50);

        return view('admin.audit.device', compact('logs'));
    }
}
