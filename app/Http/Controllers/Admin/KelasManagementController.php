<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\KelasMember;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KelasExport;
use App\Exports\KelasMemberExport;
use App\Imports\KelasImport;
use App\Imports\KelasMemberImport;

class KelasManagementController extends Controller
{
    /**
     * Display a listing of kelas
     */
    public function index(Request $request)
    {
        $query = Kelas::with(['prodi', 'membersAktif']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by prodi
        if ($request->filled('prodi_id')) {
            $query->prodi($request->prodi_id);
        }

        // Filter by angkatan
        if ($request->filled('angkatan')) {
            $query->angkatan($request->angkatan);
        }

        // Paralel filter removed per request

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == '1') {
                $query->aktif();
            } elseif ($request->status == '0') {
                $query->nonaktif();
            }
        }

        // Sort (default: earliest inserted first)
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $kelasList = $query->paginate($perPage)->withQueryString();

        // Get data for filters
        $prodiList = Prodi::aktif()->get();
        $angkatanList = Kelas::select('angkatan')->distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

        return view('admin.kelas.index', compact('kelasList', 'prodiList', 'angkatanList'));
    }

    /**
     * Show the form for creating a new kelas
     */
    public function create()
    {
        $prodiList = Prodi::aktif()->get();
        $mkList = MataKuliah::aktif()->orderBy('kode_mk')->get();
        
        return view('admin.kelas.create', compact('prodiList', 'mkList'));
    }

    /**
     * Store a newly created kelas
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:24|regex:/^[A-Z0-9\-_.]+$/',
            'nama' => 'required|string|min:3|max:100',
            'prodi_id' => 'required|exists:program_studi,id',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            // 'paralel' and 'semester_aktif' removed per request
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id',
            'kapasitas' => 'nullable|integer|min:1',
            'status' => 'required|boolean',
            'catatan' => 'nullable|string',
        ]);

        // Check unique constraint
        $exists = Kelas::where('prodi_id', $request->prodi_id)
            ->where('angkatan', $request->angkatan)
            ->where('kode', $request->kode)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Kode kelas sudah ada untuk prodi dan angkatan ini.');
        }

        $kelas = Kelas::create($request->all());

        LogHelper::create(auth()->id(), 'kelas', "Menambahkan kelas: {$kelas->nama} ({$kelas->kode})");

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Display the specified kelas
     */
    public function show(string $id)
    {
        $kelas = Kelas::with([
                'prodi',
                'members.mahasiswa',
                'sesiAbsensi',
                // gunakan jadwalKuliah (sistem baru)
                'jadwalKuliah.mataKuliah',
                'jadwalKuliah.dosen',
                'jadwalKuliah.ruangan'
            ])
            ->withCount(['membersAktif', 'members', 'jadwalKuliah as jadwal_kuliah_count', 'sesiAbsensi'])
            ->findOrFail($id);

        // Get statistik
        $statistik = [
            'total_mahasiswa_aktif' => $kelas->membersAktif()->count(),
            'total_mahasiswa_keluar' => $kelas->members()->keluar()->count(),
            'total_mahasiswa' => $kelas->members()->count(),
            'kapasitas' => $kelas->kapasitas,
            'sisa_slot' => $kelas->kapasitas ? ($kelas->kapasitas - $kelas->membersAktif()->count()) : null,
            'total_jadwal' => $kelas->jadwalKuliah()->count(),
            'total_sesi' => $kelas->sesiAbsensi()->count(),
        ];

        return view('admin.kelas.show', compact('kelas', 'statistik'));
    }

    /**
     * Show the form for editing kelas
     */
    public function edit(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        $prodiList = Prodi::aktif()->get();
        $mkList = MataKuliah::aktif()->orderBy('kode_mk')->get();
        
        return view('admin.kelas.edit', compact('kelas', 'prodiList', 'mkList'));
    }

    /**
     * Update the specified kelas
     */
    public function update(Request $request, string $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'kode' => 'required|string|max:24|regex:/^[A-Z0-9\-_.]+$/',
            'nama' => 'required|string|min:3|max:100',
            'prodi_id' => 'required|exists:program_studi,id',
            'angkatan' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'semester_aktif' => 'nullable|integer|min:1|max:14',
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id',
            'wali_dosen_id' => 'nullable|exists:users,id',
            'kapasitas' => 'nullable|integer|min:1',
            'status' => 'required|boolean',
            'catatan' => 'nullable|string',
        ]);

        // Check unique constraint (exclude current)
        $exists = Kelas::where('prodi_id', $request->prodi_id)
            ->where('angkatan', $request->angkatan)
            ->where('kode', $request->kode)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Kode kelas sudah ada untuk prodi dan angkatan ini.');
        }

        $kelas->update($request->all());

        LogHelper::update(auth()->id(), 'kelas', "Mengupdate kelas: {$kelas->nama} ({$kelas->kode})");

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diupdate.');
    }

    /**
     * Remove the specified kelas (soft delete)
     */
    public function destroy(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        
        // Cek apakah ada jadwal atau sesi aktif
        if ($kelas->jadwal()->count() > 0 || $kelas->sesiAbsensi()->count() > 0) {
            return redirect()->back()->with('error', 'Kelas tidak dapat dihapus karena masih memiliki jadwal atau sesi absensi. Nonaktifkan terlebih dahulu.');
        }

        $namaKelas = $kelas->nama;
        $kelas->delete(); // Soft delete

        LogHelper::delete(auth()->id(), 'kelas', "Menghapus kelas: {$namaKelas} ({$kelas->kode})");

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * Toggle status (aktif/nonaktif)
     */
    public function toggleStatus(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->status = !$kelas->status;
        $kelas->save();

        $statusText = $kelas->status ? 'diaktifkan' : 'dinonaktifkan';
        LogHelper::update(auth()->id(), 'kelas', "Kelas {$kelas->nama} {$statusText}");

        return redirect()->back()->with('success', "Kelas berhasil {$statusText}.");
    }

    /**
     * Rotate Wali Dosen
     */
    public function rotateWali(Request $request, string $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'wali_dosen_id' => 'required|exists:users,id',
        ]);

        $oldWali = $kelas->waliDosen ? $kelas->waliDosen->name : 'Tidak ada';
        $kelas->wali_dosen_id = $request->wali_dosen_id;
        $kelas->save();

        $newWali = $kelas->waliDosen->name;

        LogHelper::update(auth()->id(), 'kelas', "Rotate Wali Dosen {$kelas->nama}: {$oldWali} → {$newWali}");

        return redirect()->back()->with('success', 'Wali Dosen berhasil diubah.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,export',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'exists:kelas,id',
        ]);

        $ids = $request->selected_ids;

        switch ($request->action) {
            case 'activate':
                Kelas::whereIn('id', $ids)->update(['status' => 1]);
                LogHelper::update(auth()->id(), 'kelas', "Bulk activate " . count($ids) . " kelas");
                return redirect()->back()->with('success', count($ids) . ' Kelas berhasil diaktifkan.');

            case 'deactivate':
                Kelas::whereIn('id', $ids)->update(['status' => 0]);
                LogHelper::update(auth()->id(), 'kelas', "Bulk deactivate " . count($ids) . " kelas");
                return redirect()->back()->with('success', count($ids) . ' Kelas berhasil dinonaktifkan.');

            case 'export':
                $kelasList = Kelas::whereIn('id', $ids)->get();
                LogHelper::create(auth()->id(), 'kelas', "Export " . count($ids) . " kelas (selected)");
                return Excel::download(new KelasExport($kelasList), 'kelas_selected_' . date('YmdHis') . '.xlsx');
        }
    }

    /**
     * Import from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            Excel::import(new KelasImport, $request->file('file'));

            LogHelper::create(auth()->id(), 'kelas', "Import kelas dari CSV");

            return redirect()->back()->with('success', 'Kelas berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Export to CSV/XLSX
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $query = Kelas::with(['prodi']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('prodi_id')) {
            $query->prodi($request->prodi_id);
        }
        if ($request->filled('angkatan')) {
            $query->angkatan($request->angkatan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kelasList = $query->get();

        LogHelper::create(auth()->id(), 'kelas', "Export " . $kelasList->count() . " kelas ke {$format}");

        $filename = 'kelas_' . date('YmdHis') . '.' . $format;
        return Excel::download(new KelasExport($kelasList), $filename);
    }

    /**
     * Download template import
     */
    public function downloadTemplate()
    {
        $headers = [
            'kode',
            'nama',
            'prodi_id',
            'angkatan',
            'kapasitas',
            'status'
        ];

        $filename = 'template_import_kelas.csv';
        $handle = fopen('php://output', 'w');
        ob_start();
        fputcsv($handle, $headers);
        fputcsv($handle, ['TI-1A', 'Teknik Informatika 1A', '1', '2025', '40', '1']);
        fclose($handle);
        $content = ob_get_clean();

        LogHelper::create(auth()->id(), 'kelas', "Download template import kelas");

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ==================== ANGGOTA KELAS ====================

    /**
     * Show members of kelas
     */
    public function members(string $id, Request $request)
    {
        $kelas = Kelas::with(['prodi'])->findOrFail($id);
        
        $query = KelasMember::where('kelas_members.id_kelas', $id)->with('mahasiswa');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'aktif') {
                $query->aktif();
            } elseif ($request->status == 'keluar') {
                $query->keluar();
            }
        }

        $members = $query->join('mahasiswa', 'kelas_members.nim', '=', 'mahasiswa.nim')
                         ->orderBy('mahasiswa.nama', 'asc')
                         ->select('kelas_members.*')
                         ->paginate(20);

        // Get mahasiswa yang belum jadi anggota (berdasarkan tabel mahasiswa)
        $existingNims = KelasMember::where('id_kelas', $id)->aktif()->pluck('nim');
        $availableMahasiswa = \App\Models\Mahasiswa::where('id_prodi', $kelas->prodi_id)
            ->where('status_akademik', 'aktif')
            ->whereNotIn('nim', $existingNims)
            ->orderBy('nim')
            ->get();

        return view('admin.kelas.members', compact('kelas', 'members', 'availableMahasiswa'));
    }

    /**
     * Add member to kelas
     */
    public function addMember(Request $request, string $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'nim' => 'required|exists:mahasiswa,nim',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:150',
        ]);

        // Check if already active member
        $exists = KelasMember::where('id_kelas', $id)
            ->where('nim', $request->nim)
            ->aktif()
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Mahasiswa sudah menjadi anggota aktif kelas ini.');
        }

        // Check kapasitas
        if ($kelas->isFull()) {
            return redirect()->back()->with('error', 'Kelas sudah penuh (kapasitas: ' . $kelas->kapasitas . ').');
        }

        KelasMember::create([
            'id_kelas' => $id,
            'nim' => $request->nim,
            'tanggal_masuk' => $request->tanggal_masuk,
            'keterangan' => $request->keterangan,
        ]);

        // Also update mahasiswa's id_kelas
        $mhs = \App\Models\Mahasiswa::where('nim', $request->nim)->first();
        if ($mhs) {
            $mhs->update(['id_kelas' => $id]);
            LogHelper::create(auth()->id(), 'kelas', "Menambahkan {$mhs->nama} ({$mhs->nim}) ke kelas {$kelas->nama}");
        }

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Remove member from kelas (set tanggal_keluar)
     */
    public function removeMember(Request $request, string $id, string $memberId)
    {
        $member = KelasMember::where('id_kelas', $id)->findOrFail($memberId);

        $request->validate([
            'tanggal_keluar' => 'required|date|after_or_equal:' . $member->tanggal_masuk->format('Y-m-d'),
            'keterangan' => 'nullable|string|max:150',
        ]);

        $member->update([
            'tanggal_keluar' => $request->tanggal_keluar,
            'keterangan' => $request->keterangan,
        ]);

        // Also clear mahasiswa's id_kelas
        $mhs = \App\Models\Mahasiswa::where('nim', $member->nim)->first();
        if ($mhs) {
            $mhs->update(['id_kelas' => null]);
            LogHelper::update(auth()->id(), 'kelas', "Mengeluarkan {$mhs->nama} dari kelas {$member->kelas->nama}");
        }

        return redirect()->back()->with('success', 'Anggota berhasil dikeluarkan.');
    }

    /**
     * Import members from CSV
     */
    public function importMembers(Request $request, string $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            Excel::import(new KelasMemberImport($id), $request->file('file'));

            LogHelper::create(auth()->id(), 'kelas', "Import anggota kelas {$kelas->nama} dari CSV");

            return redirect()->back()->with('success', 'Anggota berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Export members to CSV/XLSX
     */
    public function exportMembers(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        $members = KelasMember::where('id_kelas', $id)->with('mahasiswa')->get();

        LogHelper::create(auth()->id(), 'kelas', "Export anggota kelas {$kelas->nama}");

        $filename = 'anggota_' . $kelas->kode . '_' . date('YmdHis') . '.xlsx';
        return Excel::download(new KelasMemberExport($members), $filename);
    }
}
