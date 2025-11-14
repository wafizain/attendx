<?php

namespace App\Http\Controllers;

use App\Models\Pertemuan;
use App\Models\JadwalKuliah;
use App\Models\Ruangan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PertemuanController extends Controller
{
    /**
     * Display listing of pertemuan for a jadwal
     */
    public function index(Request $request, $idJadwal = null)
    {
        $query = Pertemuan::with(['jadwal.mataKuliah', 'jadwal.dosen', 'ruangan', 'pembuka']);

        // Filter by jadwal
        if ($idJadwal) {
            $query->where('id_jadwal', $idJadwal);
        }

        // Filter by dosen (for dosen role)
        if (Auth::user()->role === 'dosen') {
            $query->whereHas('jadwal', function($q) {
                $q->where('id_dosen', Auth::id());
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_sesi', $request->status);
        }

        // Filter by tanggal
        if ($request->has('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter by ruangan
        if ($request->has('id_ruangan')) {
            $query->where('id_ruangan', $request->id_ruangan);
        }

        $pertemuan = $query->orderBy('tanggal', 'desc')
                          ->orderBy('jam_mulai', 'asc')
                          ->paginate(20);

        // Get jadwal info if specific jadwal
        $jadwal = $idJadwal ? JadwalKuliah::with(['mataKuliah', 'dosen', 'ruangan'])->find($idJadwal) : null;

        return view('pertemuan.index', compact('pertemuan', 'jadwal'));
    }

    /**
     * Show detail pertemuan
     */
    public function show($id)
    {
        $pertemuan = Pertemuan::with([
            'jadwal.mataKuliah',
            'jadwal.dosen',
            'jadwal.mahasiswa',
            'ruangan',
            'pembuka',
            'absensi.mahasiswa',
            'logs.user'
        ])->findOrFail($id);

        // Check auto status
        $pertemuan->checkAutoStatus();
        $pertemuan->refresh();

        // Get statistik
        $statistik = $pertemuan->getStatistikKehadiran();

        // Get window info
        $window = $pertemuan->getAbsensiWindow();

        return view('pertemuan.show', compact('pertemuan', 'statistik', 'window'));
    }

    /**
     * Open session
     */
    public function open(Request $request, $id)
    {
        $pertemuan = Pertemuan::findOrFail($id);

        // Validate permission
        if (Auth::user()->role === 'dosen' && $pertemuan->jadwal->id_dosen !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuka sesi ini.');
        }

        if ($pertemuan->status_sesi !== 'direncanakan') {
            return back()->with('error', 'Sesi sudah dibuka atau selesai.');
        }

        $openNow = $request->has('open_now');
        $pertemuan->openSession(Auth::id(), $openNow);

        return back()->with('success', 'Sesi berhasil dibuka.');
    }

    /**
     * Close session
     */
    public function close(Request $request, $id)
    {
        $pertemuan = Pertemuan::findOrFail($id);

        // Validate permission
        if (Auth::user()->role === 'dosen' && $pertemuan->jadwal->id_dosen !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menutup sesi ini.');
        }

        if ($pertemuan->status_sesi !== 'berjalan') {
            return back()->with('error', 'Sesi belum dibuka atau sudah selesai.');
        }

        $closeNow = $request->has('close_now');
        $pertemuan->closeSession(Auth::id(), $closeNow);

        return back()->with('success', 'Sesi berhasil ditutup.');
    }

    /**
     * Extend close time
     */
    public function extend(Request $request, $id)
    {
        $request->validate([
            'extend_minutes' => 'required|integer|min:1|max:120',
        ]);

        $pertemuan = Pertemuan::findOrFail($id);

        if ($pertemuan->status_sesi !== 'berjalan') {
            return back()->with('error', 'Hanya sesi yang sedang berjalan yang bisa diperpanjang.');
        }

        $window = $pertemuan->getAbsensiWindow();
        $newCloseTime = Carbon::parse($window['close'])->addMinutes($request->extend_minutes);

        $pertemuan->update(['close_at' => $newCloseTime]);

        $pertemuan->logActivity('update', 'Waktu tutup diperpanjang ' . $request->extend_minutes . ' menit', Auth::id(), [
            'old_close' => $window['close']->format('H:i'),
            'new_close' => $newCloseTime->format('H:i'),
        ]);

        return back()->with('success', 'Waktu tutup berhasil diperpanjang.');
    }

    /**
     * Cancel session
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        $pertemuan = Pertemuan::findOrFail($id);

        // Validate permission (admin only)
        if (Auth::user()->role !== 'admin') {
            return back()->with('error', 'Hanya admin yang dapat membatalkan pertemuan.');
        }

        if ($pertemuan->status_sesi === 'dibatalkan') {
            return back()->with('error', 'Pertemuan sudah dibatalkan.');
        }

        $pertemuan->cancelSession(Auth::id(), $request->void_reason);

        return back()->with('success', 'Pertemuan berhasil dibatalkan.');
    }

    /**
     * Reschedule pertemuan
     */
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'id_ruangan' => 'nullable|exists:ruangan,id',
        ]);

        $pertemuan = Pertemuan::findOrFail($id);

        // Validate permission (admin only)
        if (Auth::user()->role !== 'admin') {
            return back()->with('error', 'Hanya admin yang dapat menjadwal ulang pertemuan.');
        }

        if ($pertemuan->status_sesi !== 'direncanakan') {
            return back()->with('error', 'Hanya pertemuan yang belum dimulai yang bisa dijadwal ulang.');
        }

        // Check for conflicts if changing room
        if ($request->id_ruangan && $request->id_ruangan != $pertemuan->id_ruangan) {
            $jamMulai = $request->jam_mulai ?? $pertemuan->jam_mulai;
            $jamSelesai = $request->jam_selesai ?? $pertemuan->jam_selesai;
            
            $conflict = Pertemuan::where('id', '!=', $id)
                ->where('id_ruangan', $request->id_ruangan)
                ->whereDate('tanggal', $request->tanggal)
                ->where('status_sesi', '!=', 'dibatalkan')
                ->where(function($q) use ($jamMulai, $jamSelesai) {
                    $q->where('jam_mulai', '<', $jamSelesai)
                      ->where('jam_selesai', '>', $jamMulai);
                })
                ->exists();

            if ($conflict) {
                return back()->with('error', 'Ruangan sudah digunakan pada waktu tersebut.');
            }
        }

        $pertemuan->reschedule(
            Auth::id(),
            $request->tanggal,
            $request->jam_mulai,
            $request->jam_selesai,
            $request->id_ruangan
        );

        return back()->with('success', 'Pertemuan berhasil dijadwal ulang.');
    }

    /**
     * Update window override
     */
    public function updateWindow(Request $request, $id)
    {
        $request->validate([
            'open_min' => 'nullable|integer|min:0|max:60',
            'late_min' => 'nullable|integer|min:0|max:60',
            'close_min' => 'nullable|integer|min:0|max:180',
        ]);

        $pertemuan = Pertemuan::findOrFail($id);

        if ($pertemuan->status_sesi !== 'direncanakan') {
            return back()->with('error', 'Window hanya bisa diubah sebelum sesi dimulai.');
        }

        $pertemuan->setCustomWindow(
            $request->open_min,
            $request->late_min,
            $request->close_min
        );

        $pertemuan->logActivity('update', 'Window absensi diubah', Auth::id(), [
            'open_min' => $request->open_min,
            'late_min' => $request->late_min,
            'close_min' => $request->close_min,
        ]);

        return back()->with('success', 'Window absensi berhasil diperbarui.');
    }

    /**
     * Update materi & catatan
     */
    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'materi' => 'nullable|string|max:200',
            'catatan' => 'nullable|string|max:2000',
        ]);

        $pertemuan = Pertemuan::findOrFail($id);

        // Validate permission
        if (Auth::user()->role === 'dosen' && $pertemuan->jadwal->id_dosen !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah catatan.');
        }

        $pertemuan->update([
            'materi' => $request->materi,
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Catatan berhasil diperbarui.');
    }

    /**
     * Manual attendance correction
     */
    public function correctAttendance(Request $request, $id)
    {
        $request->validate([
            'id_mahasiswa' => 'required|exists:mahasiswa,id',
            'status' => 'required|in:hadir,telat,izin,sakit,alfa',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $pertemuan = Pertemuan::findOrFail($id);

        // Validate permission
        if (Auth::user()->role === 'dosen' && $pertemuan->jadwal->id_dosen !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah absensi.');
        }

        // Validate mahasiswa is participant
        if (!Absensi::validatePeserta($id, $request->id_mahasiswa)) {
            return back()->with('error', 'Mahasiswa bukan peserta mata kuliah ini.');
        }

        Absensi::recordAbsensi([
            'id_pertemuan' => $id,
            'id_mahasiswa' => $request->id_mahasiswa,
            'status' => $request->status,
            'waktu_scan' => now(),
            'verified_by' => 'manual',
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Absensi berhasil dikoreksi.');
    }

    /**
     * Delete absensi record
     */
    public function deleteAttendance($pertemuanId, $absensiId)
    {
        $pertemuan = Pertemuan::findOrFail($pertemuanId);
        $absensi = Absensi::where('id_pertemuan', $pertemuanId)->findOrFail($absensiId);

        // Validate permission (admin or dosen owner)
        if (Auth::user()->role === 'dosen' && $pertemuan->jadwal->id_dosen !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus absensi.');
        }

        $absensi->delete();

        return back()->with('success', 'Absensi berhasil dihapus.');
    }

    /**
     * Batch reschedule (admin only)
     */
    public function batchReschedule(Request $request)
    {
        $request->validate([
            'id_jadwal' => 'required|exists:jadwal_kuliah,id',
            'id_ruangan' => 'required|exists:ruangan,id',
            'from_minggu' => 'required|integer|min:1',
        ]);

        if (Auth::user()->role !== 'admin') {
            return back()->with('error', 'Hanya admin yang dapat melakukan reschedule massal.');
        }

        $updated = Pertemuan::where('id_jadwal', $request->id_jadwal)
            ->where('minggu_ke', '>=', $request->from_minggu)
            ->where('status_sesi', 'direncanakan')
            ->update(['id_ruangan' => $request->id_ruangan]);

        return back()->with('success', "Berhasil memindahkan {$updated} pertemuan ke ruangan baru.");
    }

    /**
     * Export rekap
     */
    public function export(Request $request, $id)
    {
        $pertemuan = Pertemuan::with([
            'jadwal.mataKuliah',
            'jadwal.dosen',
            'jadwal.mahasiswa',
            'ruangan',
            'absensi.mahasiswa'
        ])->findOrFail($id);

        $exportService = new \App\Services\PertemuanExportService();
        
        $format = $request->get('format', 'csv');
        
        switch ($format) {
            case 'excel':
                return $exportService->exportExcel($pertemuan);
            case 'pdf':
                return $exportService->exportPDF($pertemuan);
            case 'csv':
            default:
                return $exportService->exportCSV($pertemuan);
        }
    }
}
