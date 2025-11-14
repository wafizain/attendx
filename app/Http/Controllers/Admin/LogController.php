<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with(['user'])
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->paginate(50)->withQueryString();
        
        // Get unique values for filters
        $users = DB::table('users')->select('id', 'name')->orderBy('name')->get();
        $modules = Log::distinct()->pluck('module')->filter();
        $actions = Log::distinct()->pluck('action')->filter();

        return view('admin.logs.index', compact('logs', 'users', 'modules', 'actions'));
    }

    public function show($id)
    {
        $log = Log::with(['user'])->findOrFail($id);
        
        return view('admin.logs.show', compact('log'));
    }

    public function destroy($id)
    {
        try {
            $log = Log::findOrFail($id);
            $log->delete();
            
            return redirect()->route('logs.index')
                ->with('success', 'Log berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('logs.index')
                ->with('error', 'Gagal menghapus log: ' . $e->getMessage());
        }
    }

    public function clear(Request $request)
    {
        try {
            $query = Log::query();

            // Filter by date range if provided
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $deleted = $query->delete();
            
            return redirect()->route('logs.index')
                ->with('success', "Berhasil menghapus {$deleted} log.");
        } catch (\Exception $e) {
            return redirect()->route('logs.index')
                ->with('error', 'Gagal membersihkan log: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = Log::with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->get();

        $filename = "log_aktivitas_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Tanggal', 
                'User', 
                'Aksi', 
                'Module', 
                'Deskripsi',
                'IP Address'
            ]);

            // CSV Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'System',
                    $log->action,
                    $log->module ?? '-',
                    $log->description ?? '-',
                    $log->ip_address ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
