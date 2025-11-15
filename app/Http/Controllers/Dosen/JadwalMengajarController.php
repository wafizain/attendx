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
        
        // Convert hari names to Indonesian for display
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
        
        // Convert today's day to database format (1-7)
        // Carbon dayOfWeek: 0=Sunday, 1=Monday, ..., 6=Saturday
        // Database format: 1=Senin, 2=Selasa, ..., 7=Minggu
        $todayHariNumber = $today->dayOfWeek == 0 ? 7 : $today->dayOfWeek;
        
        foreach ($jadwalList as $jadwal) {
            $jadwal->pertemuan_hari_ini = Pertemuan::where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal', $today)
                ->first();
        }
        
        // Separate today's schedule and other schedules
        $jadwalHariIni = $jadwalList->filter(function($jadwal) use ($todayHariNumber) {
            return $jadwal->hari == $todayHariNumber;
        })->values();
        
        $jadwalLainnya = $jadwalList->filter(function($jadwal) use ($todayHariNumber) {
            return $jadwal->hari != $todayHariNumber;
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
        
        // Cek atau buat pertemuan hari ini
        $today = Carbon::today();
        $pertemuan = Pertemuan::where('id_jadwal', $jadwal->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$pertemuan) {
            $nextMingguKe = (int) Pertemuan::where('id_jadwal', $jadwal->id)->max('minggu_ke') + 1;

            $pertemuan = Pertemuan::create([
                'id_jadwal'   => $jadwal->id,
                'minggu_ke'   => $nextMingguKe,
                'tanggal'     => $today->format('Y-m-d'),
                'jam_mulai'   => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'id_ruangan'  => $jadwal->id_ruangan,
                'status_sesi' => 'direncanakan',
            ]);
        }

        // Jika pertemuan sudah berjalan, arahkan ke sesi absensi aktif (tanpa membuat pertemuan baru)
        if ($pertemuan->status_sesi === 'berjalan') {
            DB::beginTransaction();
            try {
                // Cari sesi absensi aktif untuk kelas & tanggal ini
                $sesiAbsensi = SesiAbsensi::where('kelas_id', $jadwal->id_kelas)
                    ->whereDate('tanggal', $today)
                    ->where('status', 'aktif')
                    ->orderByDesc('id')
                    ->first();

                // Jika belum ada sesi, buat baru
                if (!$sesiAbsensi) {
                    $sesiAbsensi = SesiAbsensi::create([
                        'kelas_id' => $jadwal->id_kelas,
                        'tanggal' => $today,
                        'topik' => $request->topik ?? 'Pertemuan ke-' . $pertemuan->minggu_ke,
                        'pertemuan_ke' => $pertemuan->minggu_ke,
                        'waktu_mulai' => Carbon::parse($today->format('Y-m-d') . ' ' . $jadwal->jam_mulai)->subMinutes($jadwal->absen_open_min ?? 10),
                        'waktu_selesai' => Carbon::parse($today->format('Y-m-d') . ' ' . $jadwal->jam_selesai),
                        'metode' => 'manual',
                        'status' => 'aktif',
                        'catatan' => 'Sesi dibuat otomatis karena pertemuan sudah berjalan',
                    ]);

                    // Inisialisasi absensi awal
                    $this->initializeAbsensi($sesiAbsensi);
                }

                DB::commit();

                return redirect()->route('dosen.jadwal-mengajar.sesi', $sesiAbsensi->id)
                    ->with('success', 'Pertemuan sudah berjalan. Anda diarahkan ke halaman kelola absensi.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal mengakses sesi absensi: ' . $e->getMessage());
            }
        }

        // Jika sudah selesai atau dibatalkan, tolak
        if (in_array($pertemuan->status_sesi, ['selesai', 'dibatalkan'])) {
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
                'waktu_mulai' => Carbon::parse($today->format('Y-m-d') . ' ' . $jadwal->jam_mulai)->subMinutes($jadwal->absen_open_min ?? 10),
                'waktu_selesai' => Carbon::parse($today->format('Y-m-d') . ' ' . $jadwal->jam_selesai)->addMinutes($jadwal->absen_close_min ?? 30),
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
        
        // Muat relasi yang diperlukan
        $sesiAbsensi = SesiAbsensi::with([
            'kelas.mataKuliah' => function($q) {
                $q->select('id', 'kode_mk', 'nama_mk');
            },
            'kelas.jadwalKuliah' => function($q) use ($dosen) {
                $q->where('id_dosen', $dosen->id);
            },
            'absensi.mahasiswa'
        ])->findOrFail($sesiId);

        // Tentukan jadwal kuliah yang menjadi sumber utama informasi
        $jadwalKuliah = null;
        $totalMahasiswa = 0;
        if ($sesiAbsensi->kelas) {
            // Cari jadwal kuliah yang sesuai dengan dosen untuk kelas ini
            $jadwalKuliah = $sesiAbsensi->kelas->jadwalKuliah
                ->where('id_dosen', $dosen->id)
                ->first();

            if ($jadwalKuliah) {
                // Gunakan relasi langsung untuk menghitung jumlah mahasiswa pada jadwal
                $totalMahasiswa = $jadwalKuliah->mahasiswa()->count();
                
                // Initialize absensi untuk semua mahasiswa di jadwal jika belum ada
                $this->initializeAbsensiFromJadwal($sesiAbsensi, $jadwalKuliah);
            } else {
                // Fallback: hitung dari relasi kelas (legacy)
                $totalMahasiswa = $sesiAbsensi->kelas->mahasiswa->count();
            }
        }

        // Check authorization
        if (!$sesiAbsensi->kelas || 
            !$sesiAbsensi->kelas->jadwalKuliah || 
            $sesiAbsensi->kelas->jadwalKuliah->isEmpty()) {
            \Log::warning('Akses ditolak ke sesi absensi', [
                'user_id' => $dosen->id,
                'kelas_id' => $sesiAbsensi->kelas_id,
                'kelas_dosen_id' => $sesiAbsensi->kelas->dosen_id ?? null,
                'jadwal_dosen_ids' => $sesiAbsensi->kelas->jadwalKuliah->pluck('id_dosen') ?? []
            ]);
            abort(403, 'Anda tidak memiliki akses ke sesi absensi ini.');
        }

        // Reload absensi after initialization
        $sesiAbsensi->load('absensi.mahasiswa');

        // Get statistics
        $stats = $sesiAbsensi->statistik;

        return view('dosen.jadwal-mengajar.kelola-absensi', [
            'sesiAbsensi' => $sesiAbsensi,
            'stats' => $stats,
            'totalMahasiswa' => $totalMahasiswa,
            'mataKuliah' => $jadwalKuliah?->mataKuliah ?? $sesiAbsensi->kelas->mataKuliah ?? null,
            'jadwal' => $jadwalKuliah,
        ]);
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
            ->where('id_mahasiswa', $mahasiswaId)
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
        
        // Load sesi tanpa filter dosen dulu
        $sesiAbsensi = SesiAbsensi::with(['kelas.jadwalKuliah', 'kelas.mataKuliah'])
            ->findOrFail($sesiId);

        // Authorization: pastikan dosen memang mengajar di kelas/jadwal ini
        if (!$sesiAbsensi->kelas ||
            !$sesiAbsensi->kelas->jadwalKuliah ||
            $sesiAbsensi->kelas->jadwalKuliah->where('id_dosen', $dosen->id)->isEmpty()) {
            \Log::warning('Akses ditolak pada tutupSesi', [
                'user_id' => $dosen->id,
                'sesi_id' => $sesiId,
                'kelas_id' => $sesiAbsensi->kelas_id ?? null
            ]);
            abort(403, 'Anda tidak memiliki akses untuk menutup sesi ini.');
        }

        if ($sesiAbsensi->status !== 'aktif') {
            return back()->with('error', 'Sesi sudah ditutup atau dibatalkan.');
        }

        // Update status sesi dan waktu selesai
        $sesiAbsensi->update([
            'status' => 'selesai',
            'waktu_selesai' => now()
        ]);

        \Log::info('Sesi absensi ditutup', [
            'sesi_id' => $sesiId,
            'dosen_id' => $dosen->id,
            'waktu_selesai' => now()
        ]);

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

        // Log aktivitas (null-safe)
        $namaMatkul = $sesiAbsensi->kelas->mataKuliah->nama_mk ?? 
                      $sesiAbsensi->kelas->nama_kelas ?? 
                      'Sesi ID: ' . $sesiId;
        
        LogHelper::update(
            $dosen->id,
            'Sesi Absensi',
            "Menutup sesi absensi: {$namaMatkul}"
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
        
        // Load sesi tanpa filter dosen dulu (sama seperti kelolaAbsensi)
        $sesiAbsensi = SesiAbsensi::with(['kelas.jadwalKuliah', 'absensi'])
            ->findOrFail($sesiId);

        // Authorization: pastikan dosen memang mengajar di kelas/jadwal ini
        if (!$sesiAbsensi->kelas ||
            !$sesiAbsensi->kelas->jadwalKuliah ||
            $sesiAbsensi->kelas->jadwalKuliah->where('id_dosen', $dosen->id)->isEmpty()) {
            \Log::warning('Akses ditolak pada absenManual', [
                'user_id' => $dosen->id,
                'kelas_id' => $sesiAbsensi->kelas_id,
                'jadwal_dosen_ids' => $sesiAbsensi->kelas->jadwalKuliah->pluck('id_dosen') ?? []
            ]);
            abort(403, 'Anda tidak memiliki akses ke sesi absensi ini.');
        }

        if ($sesiAbsensi->status !== 'aktif') {
            return back()->with('error', 'Sesi sudah ditutup atau dibatalkan.');
        }

        $updatedCount = 0;
        $allInputs = $request->all();
        
        \Log::info('Absensi manual request received', [
            'sesi_id' => $sesiId,
            'dosen_id' => $dosen->id,
            'inputs_count' => count($allInputs),
            'inputs' => $allInputs
        ]);

        DB::beginTransaction();
        try {
            // Loop through all status_* inputs
            foreach ($allInputs as $key => $value) {
                if (strpos($key, 'status_') === 0) {
                    $mahasiswaId = str_replace('status_', '', $key);
                    $newStatus = $value;
                    
                    // Validate mahasiswa ID is numeric
                    if (!is_numeric($mahasiswaId) || $mahasiswaId <= 0) {
                        \Log::warning('Invalid mahasiswa ID in absenManual', [
                            'key' => $key,
                            'mahasiswa_id' => $mahasiswaId
                        ]);
                        continue;
                    }
                    
                    if (in_array($newStatus, ['hadir', 'izin', 'sakit', 'alpha'])) {
                        // Find absensi record (gunakan hanya kolom id_mahasiswa)
                        $absensi = $sesiAbsensi->absensi()
                            ->where('id_mahasiswa', $mahasiswaId)
                            ->first();

                        if ($absensi) {
                            // Check if this is auto-attendance (fingerprint/camera) and status is hadir
                            $isAutoHadir = in_array($absensi->metode, ['fingerprint', 'camera']) && $absensi->status == 'hadir';
                            
                            // Don't allow changing auto-hadir to other status
                            if ($isAutoHadir && $newStatus != 'hadir') {
                                \Log::info('Skipping auto-hadir change', [
                                    'mahasiswa_id' => $mahasiswaId,
                                    'current_status' => $absensi->status,
                                    'new_status' => $newStatus
                                ]);
                                continue;
                            }
                            
                            // Only update if status changed
                            if ($absensi->status != $newStatus) {
                                $updateData = [
                                    'status' => $newStatus,
                                ];
                                
                                // Untuk status non-alpha, tandai sebagai absensi manual dan set waktu_scan sekarang
                                if ($newStatus != 'alpha') {
                                    $updateData['verification_method'] = 'manual';
                                    $updateData['waktu_scan'] = now();
                                } else {
                                    // Reset ke alpha: kosongkan metode manual jika ada
                                    $updateData['verification_method'] = null;
                                }
                                
                                \Log::info('Updating absensi', [
                                    'mahasiswa_id' => $mahasiswaId,
                                    'old_status' => $absensi->status,
                                    'new_status' => $newStatus,
                                    'update_data' => $updateData
                                ]);
                                
                                $absensi->update($updateData);
                                $updatedCount++;
                            }
                        } else {
                            \Log::warning('Absensi not found', [
                                'mahasiswa_id' => $mahasiswaId,
                                'sesi_id' => $sesiAbsensi->id
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            if ($updatedCount > 0) {
                return redirect()->route('dosen.jadwal-mengajar.sesi', $sesiId)
                    ->with('success', "Berhasil memperbarui absensi untuk {$updatedCount} mahasiswa.");
            } else {
                return back()->with('info', 'Tidak ada perubahan yang disimpan.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating absensi manual: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage());
        }
    }

    /**
     * Initialize absensi untuk semua mahasiswa di jadwal kuliah
     */
    private function initializeAbsensiFromJadwal(SesiAbsensi $sesiAbsensi, $jadwalKuliah)
    {
        // Ambil daftar mahasiswa yang terdaftar di jadwal kuliah
        $mahasiswaList = $jadwalKuliah->mahasiswa()->get();

        \Log::info('Initializing absensi from jadwal', [
            'sesi_id' => $sesiAbsensi->id,
            'jadwal_id' => $jadwalKuliah->id,
            'mahasiswa_count' => $mahasiswaList->count()
        ]);

        $createdCount = 0;
        foreach ($mahasiswaList as $mahasiswa) {
            // Mahasiswa model has id_user field that references users table
            $userId = $mahasiswa->id_user;
            
            if (!$userId) {
                \Log::warning('Mahasiswa without id_user', [
                    'mahasiswa_id' => $mahasiswa->id,
                    'nim' => $mahasiswa->nim ?? 'N/A'
                ]);
                continue;
            }
            
            $absensi = Absensi::firstOrCreate(
                [
                    'sesi_absensi_id' => $sesiAbsensi->id,
                    'id_mahasiswa' => $userId, // Use id_user from mahasiswa table
                ],
                [
                    'status' => 'alpha',
                ]
            );
            
            if ($absensi->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        \Log::info('Absensi initialization completed', [
            'created_count' => $createdCount,
            'total_mahasiswa' => $mahasiswaList->count()
        ]);
    }

    /**
     * Initialize absensi untuk semua mahasiswa di kelas (legacy)
     */
    private function initializeAbsensi(SesiAbsensi $sesiAbsensi)
    {
        // Ambil daftar mahasiswa (user) yang terdaftar aktif di kelas ini
        $mahasiswaList = $sesiAbsensi->kelas->mahasiswa()
            ->wherePivot('status', 'aktif')
            ->get();

        foreach ($mahasiswaList as $mahasiswa) {
            Absensi::firstOrCreate(
                [
                    'sesi_absensi_id' => $sesiAbsensi->id,
                    'id_mahasiswa' => $mahasiswa->id, // id user, sesuai FK ke tabel users
                ],
                [
                    'status' => 'alpha',
                ]
            );
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
