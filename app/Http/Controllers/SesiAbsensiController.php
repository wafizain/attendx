<?php

namespace App\Http\Controllers;

use App\Models\SesiAbsensi;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;

class SesiAbsensiController extends Controller
{
    /**
     * Display a listing of sesi absensi.
     */
    public function index(Request $request)
    {
        $query = SesiAbsensi::with(['kelas.mataKuliah', 'kelas.dosen']);

        // Filter
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $sesiAbsensi = $query->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai', 'desc')
            ->paginate(20);

        $kelasList = Kelas::with('mataKuliah')->aktif()->orderBy('nama_kelas')->get();

        return view('admin.akademik.sesi-absensi.index', compact('sesiAbsensi', 'kelasList'));
    }

    /**
     * Show the form for creating a new sesi absensi.
     */
    public function create()
    {
        $kelasList = Kelas::with('mataKuliah')->aktif()->orderBy('nama_kelas')->get();
        return view('admin.akademik.sesi-absensi.create', compact('kelasList'));
    }

    /**
     * Store a newly created sesi absensi.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'topik' => 'nullable|max:255',
            'pertemuan_ke' => 'nullable|integer|min:1',
            'waktu_mulai' => 'required|date_format:Y-m-d H:i',
            'waktu_selesai' => 'required|date_format:Y-m-d H:i|after:waktu_mulai',
            'metode' => 'required|in:manual,qr_code,geolocation',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,aktif,selesai,dibatalkan',
            'catatan' => 'nullable',
        ]);

        $data = $request->all();
        
        // Generate kode absensi jika metode QR Code
        if ($request->metode === 'qr_code') {
            $data['kode_absensi'] = SesiAbsensi::generateKodeAbsensi();
        }

        $sesiAbsensi = SesiAbsensi::create($data);

        // Jika status aktif, buat record absensi untuk semua mahasiswa di kelas
        if ($sesiAbsensi->status === 'aktif') {
            $this->initializeAbsensi($sesiAbsensi);
        }

        // Log aktivitas
        LogHelper::create('sesi_absensi', 'Membuat sesi absensi untuk kelas: ' . $sesiAbsensi->kelas->nama_kelas, [
            'sesi_absensi_id' => $sesiAbsensi->id,
            'tanggal' => $sesiAbsensi->tanggal->format('Y-m-d'),
        ]);

        return redirect()->route('sesi-absensi.show', $sesiAbsensi->id)
            ->with('success', 'Sesi absensi berhasil dibuat.');
    }

    /**
     * Display the specified sesi absensi.
     */
    public function show($id)
    {
        $sesiAbsensi = SesiAbsensi::with([
            'kelas.mataKuliah',
            'kelas.dosen',
            'absensi.mahasiswa'
        ])->findOrFail($id);

        return view('admin.akademik.sesi-absensi.show', compact('sesiAbsensi'));
    }

    /**
     * Show the form for editing sesi absensi.
     */
    public function edit($id)
    {
        $sesiAbsensi = SesiAbsensi::findOrFail($id);
        $kelasList = Kelas::with('mataKuliah')->aktif()->orderBy('nama_kelas')->get();
        
        return view('admin.akademik.sesi-absensi.edit', compact('sesiAbsensi', 'kelasList'));
    }

    /**
     * Update the specified sesi absensi.
     */
    public function update(Request $request, $id)
    {
        $sesiAbsensi = SesiAbsensi::findOrFail($id);

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'topik' => 'nullable|max:255',
            'pertemuan_ke' => 'nullable|integer|min:1',
            'waktu_mulai' => 'required|date_format:Y-m-d H:i',
            'waktu_selesai' => 'required|date_format:Y-m-d H:i|after:waktu_mulai',
            'metode' => 'required|in:manual,qr_code,geolocation',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,aktif,selesai,dibatalkan',
            'catatan' => 'nullable',
        ]);

        $oldStatus = $sesiAbsensi->status;
        $sesiAbsensi->update($request->all());

        // Jika status berubah dari draft ke aktif, initialize absensi
        if ($oldStatus === 'draft' && $request->status === 'aktif') {
            $this->initializeAbsensi($sesiAbsensi);
        }

        // Log aktivitas
        LogHelper::update('sesi_absensi', 'Mengupdate sesi absensi', [
            'sesi_absensi_id' => $sesiAbsensi->id,
        ]);

        return redirect()->route('sesi-absensi.show', $id)
            ->with('success', 'Sesi absensi berhasil diupdate.');
    }

    /**
     * Remove the specified sesi absensi.
     */
    public function destroy($id)
    {
        $sesiAbsensi = SesiAbsensi::findOrFail($id);
        $sesiAbsensi->delete();

        // Log aktivitas
        LogHelper::delete('sesi_absensi', 'Menghapus sesi absensi', [
            'sesi_absensi_id' => $id,
        ]);

        return redirect()->route('sesi-absensi.index')
            ->with('success', 'Sesi absensi berhasil dihapus.');
    }

    /**
     * Initialize absensi records for all students in the class
     */
    private function initializeAbsensi(SesiAbsensi $sesiAbsensi)
    {
        $mahasiswaList = $sesiAbsensi->kelas->mahasiswa()
            ->wherePivot('status', 'aktif')
            ->get();

        foreach ($mahasiswaList as $mahasiswa) {
            Absensi::firstOrCreate(
                [
                    'sesi_absensi_id' => $sesiAbsensi->id,
                    'id_mahasiswa' => $mahasiswa->id,
                ],
                [
                    'status' => 'alpha',
                ]
            );
        }
    }

    /**
     * Update status absensi mahasiswa
     */
    public function updateAbsensi(Request $request, $sesiId, $mahasiswaId)
    {
        $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable',
        ]);

        $absensi = Absensi::where('sesi_absensi_id', $sesiId)
            ->where('id_mahasiswa', $mahasiswaId)
            ->firstOrFail();

        $absensi->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'waktu_absen' => $request->status === 'hadir' ? now() : $absensi->waktu_absen,
        ]);

        return redirect()->back()->with('success', 'Status absensi berhasil diupdate.');
    }

    /**
     * Start sesi absensi (Mulai Kelas)
     * Mengubah semua absensi pending menjadi hadir
     */
    public function start($id)
    {
        $sesiAbsensi = SesiAbsensi::findOrFail($id);
        
        // Validasi hanya dosen yang mengajar kelas ini yang bisa memulai
        if (auth()->user()->role === 'dosen' && $sesiAbsensi->kelas->dosen_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk memulai sesi ini.');
        }
        
        // Update status sesi menjadi aktif dan catat waktu mulai
        $sesiAbsensi->update([
            'status' => 'aktif',
            'started_at' => now(),
            'started_by' => auth()->id(),
        ]);
        
        // Ubah semua absensi dengan status 'pending' menjadi 'hadir'
        $updatedCount = Absensi::where('sesi_absensi_id', $id)
            ->where('status', 'pending')
            ->update([
                'status' => 'hadir',
                'waktu_absen' => now(),
            ]);
        
        // Log aktivitas
        LogHelper::update('sesi_absensi', 'Memulai sesi absensi (Mulai Kelas)', [
            'sesi_absensi_id' => $id,
            'pending_to_hadir' => $updatedCount,
        ]);
        
        return redirect()->route('sesi-absensi.show', $id)
            ->with('success', "Sesi absensi berhasil dimulai. {$updatedCount} mahasiswa yang sudah absen sebelumnya otomatis tercatat hadir.");
    }

    /**
     * Close sesi absensi
     */
    public function close($id)
    {
        $sesiAbsensi = SesiAbsensi::findOrFail($id);
        $sesiAbsensi->update(['status' => 'selesai']);

        // Log aktivitas
        LogHelper::update('sesi_absensi', 'Menutup sesi absensi', [
            'sesi_absensi_id' => $id,
        ]);

        return redirect()->route('sesi-absensi.show', $id)
            ->with('success', 'Sesi absensi berhasil ditutup.');
    }
}
