<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RuanganController extends Controller
{
    /**
     * Display a listing of ruangan
     */
    public function index(Request $request)
    {
        $query = Ruangan::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'aktif') {
                $query->aktif();
            } elseif ($request->status == 'nonaktif') {
                $query->where('status', 'nonaktif');
            }
        }

        // Filter by lokasi
        if ($request->filled('lokasi')) {
            $query->where('lokasi', 'like', '%' . $request->lokasi . '%');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'kode');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 30);
        $ruanganList = $query->paginate($perPage)->withQueryString();

        // Get unique lokasi for filter
        $lokasiList = Ruangan::select('lokasi')->distinct()->whereNotNull('lokasi')->orderBy('lokasi')->pluck('lokasi');

        // Statistics
        $stats = [
            'total' => Ruangan::count(),
            'aktif' => Ruangan::aktif()->count(),
            'nonaktif' => Ruangan::where('status', 'nonaktif')->count(),
            'total_kapasitas' => Ruangan::aktif()->sum('kapasitas'),
        ];

        return view('admin.ruangan.index', compact('ruanganList', 'lokasiList', 'stats'));
    }

    /**
     * Show the form for creating a new ruangan
     */
    public function create()
    {
        return view('admin.ruangan.create');
    }

    /**
     * Store a newly created ruangan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:ruangan,kode',
            'nama' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:500',
            'lokasi' => 'nullable|string|max:100',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        $ruangan = Ruangan::create($validated);

        LogHelper::create(auth()->id(), 'Ruangan', 'Ruangan baru ditambahkan: ' . $ruangan->kode . ' - ' . $ruangan->nama);

        return redirect()->route('ruangan.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Display the specified ruangan
     */
    public function show($id)
    {
        $ruangan = Ruangan::with(['jadwalKuliah.mataKuliah', 'jadwalKuliah.dosen'])->findOrFail($id);

        // Get jadwal aktif
        $jadwalAktif = $ruangan->jadwalKuliah()
            ->where('status', 'aktif')
            ->with(['mataKuliah', 'dosen'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Statistics
        $stats = [
            'total_jadwal' => $ruangan->jadwalKuliah()->count(),
            'jadwal_aktif' => $ruangan->jadwalKuliah()->where('status', 'aktif')->count(),
            'utilisasi' => $this->calculateUtilisasi($ruangan),
        ];

        return view('admin.ruangan.show', compact('ruangan', 'jadwalAktif', 'stats'));
    }

    /**
     * Show the form for editing the specified ruangan
     */
    public function edit($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        return view('admin.ruangan.edit', compact('ruangan'));
    }

    /**
     * Update the specified ruangan
     */
    public function update(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'kode' => 'required|string|max:20|unique:ruangan,kode,' . $id,
            'nama' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:500',
            'lokasi' => 'nullable|string|max:100',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('ruangan.index')
                ->with('error', 'Gagal mengupdate ruangan. Periksa kembali input Anda.')
                ->withInput();
        }

        try {
            $ruangan->update($validator->validated());

            LogHelper::update(auth()->id(), 'Ruangan', 'Ruangan diupdate: ' . $ruangan->kode . ' - ' . $ruangan->nama);

            return redirect()->route('ruangan.index')
                ->with('success', 'Ruangan berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->route('ruangan.index')
                ->with('error', 'Gagal mengupdate ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified ruangan
     */
    public function destroy($id)
    {
        $ruangan = Ruangan::findOrFail($id);

        // Check if ruangan is used in jadwal
        if ($ruangan->jadwalKuliah()->where('status', 'aktif')->count() > 0) {
            return redirect()->back()
                ->with('error', 'Ruangan tidak dapat dihapus karena masih digunakan dalam jadwal aktif.');
        }

        $kodeRuangan = $ruangan->kode;
        $namaRuangan = $ruangan->nama;

        $ruangan->delete();

        LogHelper::delete(auth()->id(), 'Ruangan', 'Ruangan dihapus: ' . $kodeRuangan . ' - ' . $namaRuangan);

        return redirect()->route('ruangan.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }

    /**
     * Toggle status ruangan
     */
    public function toggleStatus($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        
        $newStatus = $ruangan->status === 'aktif' ? 'nonaktif' : 'aktif';
        $ruangan->update(['status' => $newStatus]);

        $statusText = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';
        LogHelper::update(auth()->id(), 'Ruangan', "Ruangan {$ruangan->kode} {$statusText}");

        return redirect()->back()
            ->with('success', "Ruangan berhasil {$statusText}.");
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'exists:ruangan,id',
        ]);

        $ids = $validated['selected_ids'];
        $count = count($ids);

        switch ($validated['action']) {
            case 'activate':
                Ruangan::whereIn('id', $ids)->update(['status' => 'aktif']);
                LogHelper::update(auth()->id(), 'Ruangan', "Bulk activate {$count} ruangan");
                return redirect()->back()->with('success', "{$count} ruangan berhasil diaktifkan.");

            case 'deactivate':
                Ruangan::whereIn('id', $ids)->update(['status' => 'nonaktif']);
                LogHelper::update(auth()->id(), 'Ruangan', "Bulk deactivate {$count} ruangan");
                return redirect()->back()->with('success', "{$count} ruangan berhasil dinonaktifkan.");

            case 'delete':
                // Check if any ruangan is used in active jadwal
                $usedCount = Ruangan::whereIn('id', $ids)
                    ->whereHas('jadwalKuliah', function($q) {
                        $q->where('status', 'aktif');
                    })
                    ->count();

                if ($usedCount > 0) {
                    return redirect()->back()
                        ->with('error', "{$usedCount} ruangan tidak dapat dihapus karena masih digunakan dalam jadwal aktif.");
                }

                Ruangan::whereIn('id', $ids)->delete();
                LogHelper::delete(auth()->id(), 'Ruangan', "Bulk delete {$count} ruangan");
                return redirect()->back()->with('success', "{$count} ruangan berhasil dihapus.");
        }
    }

    /**
     * Calculate room utilization percentage
     */
    private function calculateUtilisasi($ruangan)
    {
        // Total jam dalam seminggu (Senin-Jumat, 08:00-17:00 = 9 jam/hari * 5 hari = 45 jam)
        $totalJamMinggu = 45;

        // Hitung total jam terpakai dari jadwal aktif
        $jadwalAktif = $ruangan->jadwalKuliah()->where('status', 'aktif')->get();
        
        $jamTerpakai = 0;
        foreach ($jadwalAktif as $jadwal) {
            $mulai = strtotime($jadwal->jam_mulai);
            $selesai = strtotime($jadwal->jam_selesai);
            $durasi = ($selesai - $mulai) / 3600; // Convert to hours
            $jamTerpakai += $durasi;
        }

        // Calculate percentage
        $utilisasi = $totalJamMinggu > 0 ? ($jamTerpakai / $totalJamMinggu) * 100 : 0;

        return round($utilisasi, 1);
    }

    /**
     * Check availability of ruangan
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'id_ruangan' => 'required|exists:ruangan,id',
            'hari' => 'required|integer|min:1|max:7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'exclude_jadwal_id' => 'nullable|exists:jadwal_kuliah,id',
        ]);

        $ruangan = Ruangan::findOrFail($validated['id_ruangan']);

        $available = $ruangan->isAvailableAt(
            $validated['hari'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $validated['exclude_jadwal_id'] ?? null
        );

        return response()->json([
            'available' => $available,
            'message' => $available 
                ? 'Ruangan tersedia pada waktu yang dipilih.' 
                : 'Ruangan sudah digunakan pada waktu yang dipilih.',
        ]);
    }
}
