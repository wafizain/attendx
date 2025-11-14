<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\MataKuliahPengampu;
use App\Models\Prodi;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MataKuliahExport;
use App\Imports\MataKuliahImport;

class MataKuliahManagementController extends Controller
{
    /**
     * Display a listing of mata kuliah
     */
    public function index(Request $request)
    {
        $query = MataKuliah::with(['prodi', 'pengampu.dosen']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by prodi
        if ($request->filled('prodi_id')) {
            $query->prodi($request->prodi_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->aktif();
        } elseif ($request->status == '0') {
            $query->nonaktif();
        }

        $mataKuliahList = $query->orderBy('nama_mk')->paginate(30);

        $prodiList = Prodi::aktif()->get();

        return view('admin.mata-kuliah.index', compact('mataKuliahList', 'prodiList'));
    }

    /**
     * Show the form for creating mata kuliah
     */
    public function create()
    {
        $prodiList = Prodi::aktif()->get();
        return view('admin.mata-kuliah.create', compact('prodiList'));
    }

    /**
     * Store a newly created mata kuliah
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_prodi' => 'required|exists:program_studi,id',
            'kurikulum' => 'required|regex:/^[A-Za-z0-9-_]{2,20}$/',
            'kode_mk' => 'required|regex:/^[A-Za-z0-9._-]{2,20}$/',
            'nama_mk' => 'required|min:3|max:150',
            'sks' => 'required|integer|min:1|max:6',
            // 'jenis' removed from CRUD
            'semester' => 'required|integer|min:1|max:14',
            'deskripsi' => 'nullable|string',
            'prasyarat' => 'nullable|json',
            'kode_eksternal' => 'nullable|string|max:32',
        ]);

        $data = $request->all();
        $data['status'] = 1; // Auto-set status to active

        $mataKuliah = MataKuliah::create($data);

        LogHelper::create('mata_kuliah', "Membuat mata kuliah: {$mataKuliah->nama_mk}");

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil ditambahkan.');
    }

    /**
     * Display the specified mata kuliah
     */
    public function show(string $id)
    {
        $mataKuliah = MataKuliah::with(['prodi', 'pengampu.dosen', 'kelas', 'jadwalKuliah.dosen', 'jadwalKuliah.ruangan'])
            ->withCount(['pengampu', 'kelas'])
            ->findOrFail($id);

        // Get statistik
        $statistik = [
            'total_kelas' => $mataKuliah->kelas()->count(),
            'total_kelas_aktif' => $mataKuliah->kelas()->where('status', 1)->count(),
            'total_pengampu' => $mataKuliah->pengampu()->count(),
            'total_mahasiswa' => $mataKuliah->kelas()->withCount('membersAktif')->get()->sum('members_aktif_count'),
            'total_jadwal' => $mataKuliah->jadwalKuliah()->count(),
            'total_jadwal_aktif' => $mataKuliah->jadwalKuliah()->where('status', 'aktif')->count(),
        ];

        // Get jadwal aktif
        $jadwalAktif = $mataKuliah->jadwalKuliah()
            ->where('status', 'aktif')
            ->with(['dosen', 'ruangan', 'kelas'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(30);

        // Get dosen yang belum jadi pengampu
        $existingDosenIds = MataKuliahPengampu::where('id_mk', $id)->pluck('dosen_id');
        $availableDosen = User::where('role', 'dosen')
            ->where('status', 'aktif')
            ->whereNotIn('id', $existingDosenIds)
            ->get();

        // Get logs
        $logs = \App\Models\Log::where('module', 'mata_kuliah')
            ->where(function($q) use ($mataKuliah) {
                $q->where('description', 'like', "%{$mataKuliah->kode_mk}%")
                  ->orWhere('description', 'like', "%{$mataKuliah->nama_mk}%");
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.mata-kuliah.show', compact('mataKuliah', 'statistik', 'jadwalAktif', 'logs', 'availableDosen'));
    }

    /**
     * Show the form for editing mata kuliah
     */
    public function edit(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        $prodiList = Prodi::aktif()->get();
        
        return view('admin.mata-kuliah.edit', compact('mataKuliah', 'prodiList'));
    }

    /**
     * Update the specified mata kuliah
     */
    public function update(Request $request, string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        $request->validate([
            'id_prodi' => 'required|exists:program_studi,id',
            'kurikulum' => 'required|regex:/^[A-Za-z0-9-_]{2,20}$/',
            'kode_mk' => 'required|regex:/^[A-Za-z0-9._-]{2,20}$/',
            'nama_mk' => 'required|min:3|max:150',
            'sks' => 'required|integer|min:1|max:6',
            // 'jenis' removed from CRUD
            'semester' => 'required|integer|min:1|max:14',
            'deskripsi' => 'nullable|string',
            'prasyarat' => 'nullable|json',
            'kode_eksternal' => 'nullable|string|max:32',
        ]);

        $updateData = $request->except(['_token', '_method']);
        // Status tidak diupdate melalui form edit
        
        $mataKuliah->update($updateData);

        LogHelper::update('mata_kuliah', "Mengupdate mata kuliah: {$mataKuliah->nama_mk}");

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil diupdate.');
    }

    /**
     * Remove the specified mata kuliah
     */
    public function destroy(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        // Check if mata kuliah has active classes
        $hasActiveClasses = $mataKuliah->kelas()->where('status', 1)->exists();
        if ($hasActiveClasses) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus mata kuliah yang memiliki kelas aktif.');
        }

        $mataKuliah->delete();

        LogHelper::delete('mata_kuliah', "Menghapus mata kuliah: {$mataKuliah->nama_mk}");

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil dihapus.');
    }

    /**
     * Toggle status (aktif/nonaktif)
     */
    public function toggleStatus(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        $mataKuliah->status = !$mataKuliah->status;
        $mataKuliah->save();

        $statusText = $mataKuliah->status ? 'diaktifkan' : 'dinonaktifkan';
        LogHelper::update('mata_kuliah', "Mata kuliah {$mataKuliah->nama_mk} {$statusText}");

        return redirect()->back()->with('success', "Mata Kuliah berhasil {$statusText}.");
    }

    /**
     * Add pengampu to mata kuliah
     */
    public function addPengampu(Request $request, string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        
        $request->validate([
            'id_dosen' => 'required|exists:users,id'
        ]);

        $dosen = User::findOrFail($request->id_dosen);
        
        // Check if already pengampu
        $exists = MataKuliahPengampu::where('id_mk', $id)
            ->where('dosen_id', $dosen->id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()->with('error', 'Dosen sudah menjadi pengampu mata kuliah ini.');
        }

        // Add pengampu
        MataKuliahPengampu::create([
            'id_mk' => $id,
            'dosen_id' => $dosen->id,
            'peran' => 'Pengampu Utama',
            'bobot_persen' => null
        ]);

        LogHelper::create('mata_kuliah', "Menambahkan pengampu: {$dosen->name} pada mata kuliah {$mataKuliah->nama_mk}");

        return redirect()->back()->with('success', 'Dosen pengampu berhasil ditambahkan.');
    }

    /**
     * Remove pengampu from mata kuliah
     */
    public function removePengampu(string $id, string $pengampuId)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        $pengampu = MataKuliahPengampu::findOrFail($pengampuId);
        
        // Delete pengampu
        $pengampu->delete();

        LogHelper::delete('mata_kuliah', "Menghapus pengampu: {$pengampu->dosen->name} dari mata kuliah {$mataKuliah->nama_mk}");

        return redirect()->back()->with('success', 'Dosen pengampu berhasil dihapus.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,export',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'exists:mata_kuliah,id',
        ]);

        $ids = $request->selected_ids;

        switch ($request->action) {
            case 'activate':
                MataKuliah::whereIn('id', $ids)->update(['status' => 1]);
                LogHelper::update('mata_kuliah', "Bulk activate " . count($ids) . " mata kuliah");
                return redirect()->back()->with('success', count($ids) . ' Mata Kuliah berhasil diaktifkan.');

            case 'deactivate':
                MataKuliah::whereIn('id', $ids)->update(['status' => 0]);
                LogHelper::update('mata_kuliah', "Bulk deactivate " . count($ids) . " mata kuliah");
                return redirect()->back()->with('success', count($ids) . ' Mata Kuliah berhasil dinonaktifkan.');

            case 'export':
                $mataKuliahList = MataKuliah::whereIn('id', $ids)->get();
                LogHelper::create('mata_kuliah', "Export " . count($ids) . " mata kuliah (selected)");
                return Excel::download(new MataKuliahExport($mataKuliahList), 'mata_kuliah_selected_' . date('YmdHis') . '.xlsx');
        }
    }
}
