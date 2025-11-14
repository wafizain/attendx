<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArsipController extends Controller
{
    /**
     * Display a listing of archived items.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'mahasiswa');
        $search = $request->get('search');
        
        $data = [];
        
        if ($type === 'mahasiswa') {
            $query = Mahasiswa::onlyTrashed()
                ->with(['prodi', 'kelas', 'user']);
                
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%");
                });
            }
            
            $data = $query->orderBy('deleted_at', 'desc')->paginate(10);
            
        } elseif ($type === 'dosen') {
            $query = Dosen::onlyTrashed()
                ->with(['prodi', 'user']);
                
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            }
            
            $data = $query->orderBy('deleted_at', 'desc')->paginate(10);
        }
        
        return view('admin.arsip.index', compact('data', 'type', 'search'));
    }
    
    /**
     * Restore the specified archived item.
     */
    public function restore($type, $id)
    {
        try {
            DB::beginTransaction();
            
            if ($type === 'mahasiswa') {
                $item = Mahasiswa::onlyTrashed()->findOrFail($id);
                
                // Restore status to aktif
                $item->update(['status_akademik' => 'aktif']);
                
                // Restore the mahasiswa
                $item->restore();
                
                // Also restore user if exists
                if ($item->id_user) {
                    $user = User::find($item->id_user);
                    if ($user) {
                        // Check if User model supports soft deletes
                        if (method_exists($user, 'trashed') && $user->trashed()) {
                            $user->restore();
                        }
                        // If user doesn't support soft deletes, no action needed
                    }
                }
                
                Log::info('Mahasiswa restored from archive', ['id' => $id, 'nama' => $item->nama, 'status' => 'aktif']);
                
            } elseif ($type === 'dosen') {
                $item = Dosen::onlyTrashed()->findOrFail($id);
                $item->restore();
                
                // Also restore user if exists
                if ($item->id_user) {
                    $user = User::find($item->id_user);
                    if ($user) {
                        // Check if User model supports soft deletes
                        if (method_exists($user, 'trashed') && $user->trashed()) {
                            $user->restore();
                        }
                        // If user doesn't support soft deletes, no action needed
                    }
                }
                
                Log::info('Dosen restored from archive', ['id' => $id, 'nama' => $item->nama]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Data berhasil dikembalikan dari arsip.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore from archive', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengembalikan data dari arsip: ' . $e->getMessage());
        }
    }
    
    /**
     * Permanently delete the specified archived item.
     */
    public function permanentDelete($type, $id)
    {
        try {
            DB::beginTransaction();
            
            if ($type === 'mahasiswa') {
                $item = Mahasiswa::onlyTrashed()->findOrFail($id);
                $nama = $item->nama;
                
                // Also delete user if exists
                if ($item->id_user) {
                    $user = User::find($item->id_user);
                    if ($user) {
                        // Check if User model supports soft deletes
                        if (method_exists($user, 'trashed') && $user->trashed()) {
                            $user->forceDelete();
                        } else {
                            // Just delete the user normally if not soft deleted
                            $user->delete();
                        }
                    }
                }
                
                $item->forceDelete();
                
                Log::info('Mahasiswa permanently deleted from archive', ['id' => $id, 'nama' => $nama]);
                
            } elseif ($type === 'dosen') {
                $item = Dosen::onlyTrashed()->findOrFail($id);
                $nama = $item->nama;
                
                // Also delete user if exists
                if ($item->id_user) {
                    $user = User::find($item->id_user);
                    if ($user) {
                        // Check if User model supports soft deletes
                        if (method_exists($user, 'trashed') && $user->trashed()) {
                            $user->forceDelete();
                        } else {
                            // Just delete the user normally if not soft deleted
                            $user->delete();
                        }
                    }
                }
                
                $item->forceDelete();
                
                Log::info('Dosen permanently deleted from archive', ['id' => $id, 'nama' => $nama]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Data berhasil dihapus permanen dari arsip.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to permanently delete from archive', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus permanen data dari arsip: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle bulk actions on archived items.
     */
    public function bulkAction(Request $request)
    {
        $action = $request->get('action');
        $ids = $request->get('ids', []);
        $type = $request->get('type', 'mahasiswa');
        
        // Convert comma-separated string to array if needed
        if (is_string($ids)) {
            $ids = explode(',', $ids);
            $ids = array_filter($ids, 'is_numeric'); // Filter out non-numeric values
            $ids = array_map('intval', $ids); // Convert to integers
        }
        
        if (empty($ids) || !in_array($action, ['restore', 'permanent_delete'])) {
            return back()->with('error', 'Aksi tidak valid atau tidak ada item yang dipilih.');
        }
        
        // Validate type
        if (!in_array($type, ['mahasiswa', 'dosen'])) {
            return back()->with('error', 'Tipe data tidak valid.');
        }
        
        try {
            DB::beginTransaction();
            
            $count = 0;
            
            foreach ($ids as $id) {
                if ($action === 'restore') {
                    $this->restore($type, $id);
                } elseif ($action === 'permanent_delete') {
                    $this->permanentDelete($type, $id);
                }
                $count++;
            }
            
            DB::commit();
            
            $message = $action === 'restore' 
                ? "Berhasil mengembalikan {$count} data dari arsip."
                : "Berhasil menghapus permanen {$count} data dari arsip.";
                
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk action failed on archive', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal melakukan aksi bulk: ' . $e->getMessage());
        }
    }
}
