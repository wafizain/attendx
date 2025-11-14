<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JadwalMengajarController extends Controller
{
    public function __construct()
    {
        // Middleware ditangani di routes
    }

    /**
     * Display jadwal mengajar dosen
     */
    public function index(Request $request)
    {
        // Log debug info
        Log::info('JadwalMengajarController::index called', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()?->role,
            'request_data' => $request->all(),
            'url' => $request->fullUrl()
        ]);

        $dosen = Auth::user();
        
        // User sudah dipastikan login dan role dosen oleh middleware
        if (!$dosen) {
            Log::error('JadwalMengajar: User not found', ['auth_id' => Auth::id()]);
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($dosen->role !== 'dosen') {
            Log::error('JadwalMengajar: User not dosen', [
                'user_id' => $dosen->id,
                'user_role' => $dosen->role
            ]);
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        
        Log::info('JadwalMengajar: User validated', [
            'dosen_id' => $dosen->id,
            'dosen_name' => $dosen->name
        ]);
        
        // Get jadwal kuliah yang diampu dosen
        $query = JadwalKuliah::with(['mataKuliah', 'kelas', 'ruangan', 'mahasiswa'])
            ->where('id_dosen', $dosen->id)
            ->where('status', 'aktif');

        // Filter berdasarkan hari jika ada
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $jadwalList = $query->orderBy('hari', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        Log::info('JadwalMengajar: Jadwal retrieved', [
            'dosen_id' => $dosen->id,
            'jadwal_count' => $jadwalList->count()
        ]);

        // Get pertemuan hari ini untuk setiap jadwal
        $today = Carbon::today();
        $todayName = strtolower($today->format('l'));
        
        // Convert hari names to Indonesian
        $dayMapping = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        $todayIndo = $dayMapping[$todayName] ?? 'senin';
        
        foreach ($jadwalList as $jadwal) {
            $jadwal->pertemuan_hari_ini = Pertemuan::where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal', $today)
                ->first();
        }
        
        // Separate today's schedule and other schedules
        $jadwalHariIni = $jadwalList->filter(function($jadwal) use ($todayIndo) {
            return strtolower($jadwal->hari) == $todayIndo;
        })->values();
        
        $jadwalLainnya = $jadwalList->filter(function($jadwal) use ($todayIndo) {
            return strtolower($jadwal->hari) != $todayIndo;
        })->values();

        return view('dosen.jadwal-mengajar.index', compact('jadwalHariIni', 'jadwalLainnya', 'todayIndo'));
    }

    /**
     * Show detail jadwal mengajar
     */
    public function show($id)
    {
        $dosen = Auth::user();
        
        $jadwal = JadwalKuliah::with(['mataKuliah', 'kelas', 'ruangan', 'mahasiswa', 'pertemuan'])
            ->where('id_dosen', $dosen->id)
            ->findOrFail($id);

        // Get pertemuan terbaru
        $pertemuanTerbaru = $jadwal->pertemuan()
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        // Get statistik kehadiran
        $statistik = $this->getStatistikKehadiran($jadwal);

        return view('dosen.jadwal-mengajar.show', compact('jadwal', 'pertemuanTerbaru', 'statistik'));
    }

    /**
     * Mulai kelas dan buka sesi absensi otomatis
     */
    public function mulaiKelas(Request $request, $id)
    {
        $dosen = Auth::user();
        
        $jadwal = JadwalKuliah::where('id_dosen', $dosen->id)->findOrFail($id);
        
        // Cek apakah ada pertemuan hari ini
        $today = Carbon::today();
        $pertemuan = Pertemuan::where('id_jadwal', $jadwal->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$pertemuan) {
            return back()->with('error', 'Tidak ada pertemuan yang dijadwalkan hari ini.');
        }

        if ($pertemuan->status_sesi !== 'direncanakan') {
            return back()->with('error', 'Pertemuan sudah dimulai atau selesai.');
        }

        DB::beginTransaction();
        try {
            // Buka sesi pertemuan
            $pertemuan->openSession($dosen->id, true);

            // Buat sesi absensi otomatis
            $sesiAbsensi = SesiAbsensi::create([
                'kelas_id' => $jadwal->id_kelas,
                'tanggal' => $today,
                'topik' => $request->topik ?? 'Pertemuan ke-' . $pertemuan->minggu_ke,
                'pertemuan_ke' => $pertemuan->minggu_ke,
                'waktu_mulai' => Carbon::parse($today->format('Y-m-d') . ' ' . $jadwal->jam_mulai)->subMinutes(10),
                'waktu_selesai' => Carbon::parse($today->format('Y-m-d') . ' ' . $jadwal->jam_selesai),
                'metode' => 'manual', // Default manual, bisa diubah
                'status' => 'aktif',
                'catatan' => 'Sesi dibuka otomatis oleh dosen',
            ]);

            // Initialize absensi untuk semua mahasiswa
            $this->initializeAbsensi($sesiAbsensi);

            // Log aktivitas
            LogHelper::create(
                $dosen->id,
                'Jadwal Mengajar',
                "Memulai kelas: {$jadwal->mataKuliah->nama_mk} - {$jadwal->kelas->nama}"
            );

            DB::commit();

            return redirect()->route('dosen.jadwal-mengajar.sesi', $sesiAbsensi->id)
                ->with('success', 'Kelas berhasil dimulai dan sesi absensi telah dibuka.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai kelas: ' . $e->getMessage());
        }
    }

    /**
     * Kelola sesi absensi
     */
    public function kelolaAbsensi($sesiId)
    {
        $dosen = Auth::user();
        
        $sesiAbsensi = SesiAbsensi::with(['kelas.mataKuliah', 'kelas.dosen', 'absensi.mahasiswa'])
            ->whereHas('kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            ->findOrFail($sesiId);

        // Get statistik
        $stats = $sesiAbsensi->statistik;

        return view('dosen.jadwal-mengajar.kelola-absensi', compact('sesiAbsensi', 'stats'));
    }

    /**
     * Update absensi mahasiswa (absen manual)
     */
    public function updateAbsensi(Request $request, $sesiId, $mahasiswaId)
    {
        $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $dosen = Auth::user();
        
        $sesiAbsensi = SesiAbsensi::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->findOrFail($sesiId);

        $absensi = Absensi::where('sesi_absensi_id', $sesiId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->firstOrFail();

        $oldStatus = $absensi->status;
        
        $absensi->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'waktu_absen' => $request->status === 'hadir' ? now() : $absensi->waktu_absen,
            'metode_absen' => 'manual',
            'verified_by' => 'dosen',
        ]);

        // Log aktivitas
        LogHelper::update(
            $dosen->id,
            'Absensi Manual',
            "Update absensi mahasiswa: {$absensi->mahasiswa->nama} dari {$oldStatus} ke {$request->status}"
        );

        return back()->with('success', 'Status absensi berhasil diupdate.');
    }

    /**
     * Tutup sesi absensi
     */
    public function tutupSesi($sesiId)
    {
        $dosen = Auth::user();
        
        $sesiAbsensi = SesiAbsensi::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->findOrFail($sesiId);

        if ($sesiAbsensi->status !== 'aktif') {
            return back()->with('error', 'Sesi sudah ditutup atau dibatalkan.');
        }

        $sesiAbsensi->update(['status' => 'selesai']);

        // Tutup pertemuan juga jika ada
        $pertemuan = Pertemuan::whereHas('jadwal', function($q) use ($dosen) {
            $q->where('id_dosen', $dosen->id);
        })
        ->whereDate('tanggal', $sesiAbsensi->tanggal)
        ->where('status_sesi', 'berjalan')
        ->first();

        if ($pertemuan) {
            $pertemuan->closeSession($dosen->id, true);
        }

        // Log aktivitas
        LogHelper::update(
            $dosen->id,
            'Sesi Absensi',
            "Menutup sesi absensi: {$sesiAbsensi->kelas->mataKuliah->nama_mk}"
        );

        return redirect()->route('dosen.jadwal-mengajar.index')
            ->with('success', 'Sesi absensi berhasil ditutup.');
    }

    /**
     * Absensi manual mahasiswa (bulk)
     */
    public function absenManual(Request $request, $sesiId)
    {
        $dosen = Auth::user();
        
        $sesiAbsensi = SesiAbsensi::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->findOrFail($sesiId);

        if ($sesiAbsensi->status !== 'aktif') {
            return back()->with('error', 'Sesi sudah ditutup atau dibatalkan.');
        }

        $mahasiswaIds = $request->input('mahasiswa_id', []);
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($mahasiswaIds as $mahasiswaId) {
                $status = $request->input('status_' . $mahasiswaId);
                
                if ($status && in_array($status, ['hadir', 'izin', 'sakit'])) {
                    $absensi = $sesiAbsensi->absensi()
                        ->where('mahasiswa_id', $mahasiswaId)
                        ->first();

                    if ($absensi) {
                        $absensi->update([
                            'status' => $status,
                            'metode' => 'manual',
                            'waktu_absen' => now(),
                            'updated_by' => $dosen->id
                        ]);
                        $updatedCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->route('dosen.jadwal-mengajar.sesi', $sesiId)
                ->with('success', "Berhasil memperbarui absensi untuk {$updatedCount} mahasiswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage());
        }
    }

    /**
     * Initialize absensi untuk semua mahasiswa di kelas
     */
    private function initializeAbsensi(SesiAbsensi $sesiAbsensi)
    {
        // Get mahasiswa dari jadwal kuliah yang terkait dengan kelas ini
        $jadwal = JadwalKuliah::where('id_kelas', $sesiAbsensi->kelas_id)->first();
        
        if ($jadwal) {
            $mahasiswaList = $jadwal->mahasiswa;
        } else {
            // Fallback: ambil dari kelas members yang aktif
            $mahasiswaList = \App\Models\KelasMember::where('id_kelas', $sesiAbsensi->kelas_id)
                ->aktif()
                ->with('mahasiswa')
                ->get()
                ->pluck('mahasiswa');
        }

        foreach ($mahasiswaList as $mahasiswa) {
            if ($mahasiswa) {
                Absensi::firstOrCreate(
                    [
                        'sesi_absensi_id' => $sesiAbsensi->id,
                        'mahasiswa_id' => $mahasiswa->id,
                    ],
                    [
                        'status' => 'alpha',
                    ]
                );
            }
        }
    }

    /**
     * Get statistik kehadiran untuk jadwal
     */
    private function getStatistikKehadiran($jadwal)
    {
        $totalPertemuan = $jadwal->pertemuan()->where('status_sesi', 'selesai')->count();
        $totalMahasiswa = $jadwal->mahasiswa()->count();
        
        if ($totalPertemuan == 0 || $totalMahasiswa == 0) {
            return [
                'total_pertemuan' => $totalPertemuan,
                'total_mahasiswa' => $totalMahasiswa,
                'rata_kehadiran' => 0,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
            ];
        }

        // Hitung statistik dari sesi absensi
        $absensiData = DB::table('absensi')
            ->join('sesi_absensi', 'absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->where('sesi_absensi.kelas_id', $jadwal->id_kelas)
            ->where('sesi_absensi.status', 'selesai')
            ->selectRaw('
                COUNT(CASE WHEN absensi.status = "hadir" THEN 1 END) as hadir,
                COUNT(CASE WHEN absensi.status = "izin" THEN 1 END) as izin,
                COUNT(CASE WHEN absensi.status = "sakit" THEN 1 END) as sakit,
                COUNT(CASE WHEN absensi.status = "alpha" THEN 1 END) as alpha,
                COUNT(*) as total
            ')
            ->first();

        $rataKehadiran = $absensiData->total > 0 
            ? round((($absensiData->hadir + $absensiData->izin + $absensiData->sakit) / $absensiData->total) * 100, 2)
            : 0;

        return [
            'total_pertemuan' => $totalPertemuan,
            'total_mahasiswa' => $totalMahasiswa,
            'rata_kehadiran' => $rataKehadiran,
            'hadir' => $absensiData->hadir,
            'izin' => $absensiData->izin,
            'sakit' => $absensiData->sakit,
            'alpha' => $absensiData->alpha,
        ];
    }
}
