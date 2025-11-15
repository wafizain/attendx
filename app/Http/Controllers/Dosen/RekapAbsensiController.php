<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use App\Models\SesiAbsensi;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\Semester;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RekapAbsensiController extends Controller
{
    public function __construct()
    {
        // Middleware ditangani di routes
    }

    /**
     * Display rekap absensi mahasiswa per semester
     */
    public function index(Request $request)
    {
        $dosen = Auth::user();
        
        // Get semester aktif
        $semesterAktif = Semester::where('status', 'aktif')->first();
        $semesterList = Semester::orderBy('tahun_ajaran', 'desc')->orderBy('semester', 'desc')->get();
        
        // Get jadwal dosen
        $jadwalList = JadwalKuliah::with(['mataKuliah', 'kelas', 'mahasiswa'])
            ->where('id_dosen', $dosen->id)
            ->where('status', 'aktif');

        // Filter berdasarkan semester
        $semesterId = $request->filled('semester_id') ? $request->semester_id : ($semesterAktif ? $semesterAktif->id : null);
        if ($semesterId && Schema::hasColumn('jadwal_kuliah', 'semester_id')) {
            $jadwalList->where('semester_id', $semesterId);
        }

        $jadwalList = $jadwalList->get();

        // Get rekap absensi per jadwal
        $rekapData = [];
        foreach ($jadwalList as $jadwal) {
            $rekap = $this->getRekapAbsensiPerJadwal($jadwal, $semesterId);
            if ($rekap) {
                $rekapData[] = $rekap;
            }
        }

        return view('dosen.rekap-absensi.index', compact('rekapData', 'semesterList', 'semesterAktif', 'semesterId'));
    }

    /**
     * Display detail rekap absensi per kelas
     */
    public function show($jadwalId, Request $request)
    {
        $dosen = Auth::user();
        
        $jadwal = JadwalKuliah::with(['mataKuliah', 'kelas', 'mahasiswa'])
            ->where('id_dosen', $dosen->id)
            ->findOrFail($jadwalId);

        // Filter berdasarkan semester
        $semesterId = $request->filled('semester_id') ? $request->semester_id : null;
        
        // Get sesi absensi untuk kelas ini (yang sebenarnya digunakan untuk absensi)
        $sesiQuery = SesiAbsensi::where('kelas_id', $jadwal->id_kelas)
            ->where('status', 'selesai');
        
        // Filter by semester if provided
        if ($semesterId) {
            $semester = Semester::find($semesterId);
            if ($semester) {
                $sesiQuery->whereDate('tanggal', '>=', $semester->tanggal_mulai ?? now()->startOfYear())
                          ->whereDate('tanggal', '<=', $semester->tanggal_selesai ?? now()->endOfYear());
            }
        }
        
        $pertemuanList = $sesiQuery->orderBy('tanggal', 'asc')->get();

        // Get rekap per mahasiswa
        $rekapMahasiswa = [];
        foreach ($jadwal->mahasiswa as $mahasiswa) {
            // Use id_user (FK to users table), not mahasiswa.id (PK)
            $rekap = $this->getRekapAbsensiPerMahasiswa($mahasiswa->id_user, $jadwal->id, $semesterId);
            $rekap['mahasiswa'] = $mahasiswa;
            $rekapMahasiswa[] = $rekap;
        }

        // Sort by persentase kehadiran (descending)
        usort($rekapMahasiswa, function($a, $b) {
            return $b['persentase_kehadiran'] <=> $a['persentase_kehadiran'];
        });

        return view('dosen.rekap-absensi.show', compact('jadwal', 'pertemuanList', 'rekapMahasiswa', 'semesterId'));
    }

    /**
     * Export rekap absensi
     */
    public function export($jadwalId, $format, Request $request)
    {
        $dosen = Auth::user();
        
        $jadwal = JadwalKuliah::with(['mataKuliah', 'kelas'])
            ->where('id_dosen', $dosen->id)
            ->findOrFail($jadwalId);

        $semesterId = $request->filled('semester_id') ? $request->semester_id : null;

        if ($format === 'excel') {
            return $this->exportExcel($jadwal, $semesterId);
        } else {
            return $this->exportPDF($jadwal, $semesterId);
        }
    }

    /**
     * Get rekap absensi per jadwal
     */
    private function getRekapAbsensiPerJadwal($jadwal, $semesterId)
    {
        if (!$jadwal->id_kelas) {
            return null;
        }

        // Get sesi absensi untuk kelas ini yang sudah selesai
        $sesiQuery = SesiAbsensi::where('kelas_id', $jadwal->id_kelas)
            ->where('status', 'selesai');
        
        // Filter by semester if provided
        if ($semesterId) {
            $semester = Semester::find($semesterId);
            if ($semester) {
                $sesiQuery->whereDate('tanggal', '>=', $semester->tanggal_mulai ?? now()->startOfYear())
                          ->whereDate('tanggal', '<=', $semester->tanggal_selesai ?? now()->endOfYear());
            }
        }
        
        $pertemuanList = $sesiQuery->get();

        if ($pertemuanList->isEmpty()) {
            return null;
        }

        $totalPertemuan = $pertemuanList->count();
        $totalMahasiswa = $jadwal->mahasiswa->count();
        
        $rekap = [
            'jadwal' => $jadwal,
            'total_pertemuan' => $totalPertemuan,
            'total_mahasiswa' => $totalMahasiswa,
            'statistik' => [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
                'persentase_kehadiran' => 0
            ]
        ];

        $totalKehadiran = 0;
        $totalAbsensi = 0;

        foreach ($jadwal->mahasiswa as $mahasiswa) {
            // Use id_user (FK to users table), not mahasiswa.id (PK)
            $statMahasiswa = $this->getRekapAbsensiPerMahasiswa($mahasiswa->id_user, $jadwal->id, $semesterId);
            
            $rekap['statistik']['hadir'] += $statMahasiswa['hadir'];
            $rekap['statistik']['izin'] += $statMahasiswa['izin'];
            $rekap['statistik']['sakit'] += $statMahasiswa['sakit'];
            $rekap['statistik']['alpha'] += $statMahasiswa['alpha'];
            
            $totalKehadiran += $statMahasiswa['hadir'];
            $totalAbsensi += $statMahasiswa['total'];
        }

        // Hitung persentase kehadiran rata-rata
        if ($totalAbsensi > 0) {
            $rekap['statistik']['persentase_kehadiran'] = round(($totalKehadiran / $totalAbsensi) * 100, 2);
        }

        return $rekap;
    }

    /**
     * Get rekap absensi per mahasiswa
     */
    private function getRekapAbsensiPerMahasiswa($mahasiswaId, $jadwalId, $semesterId)
    {
        // Get jadwal untuk ambil kelas_id
        $jadwal = JadwalKuliah::find($jadwalId);
        if (!$jadwal || !$jadwal->id_kelas) {
            return [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
                'total' => 0,
                'persentase_kehadiran' => 0
            ];
        }

        // Get sesi absensi untuk kelas ini yang sudah selesai
        $sesiQuery = SesiAbsensi::where('kelas_id', $jadwal->id_kelas)
            ->where('status', 'selesai');
        
        // Filter by semester if provided (via tanggal)
        if ($semesterId) {
            $semester = Semester::find($semesterId);
            if ($semester) {
                // Assuming semester has tanggal_mulai and tanggal_selesai
                // If not, we filter by year/semester number
                $sesiQuery->whereDate('tanggal', '>=', $semester->tanggal_mulai ?? now()->startOfYear())
                          ->whereDate('tanggal', '<=', $semester->tanggal_selesai ?? now()->endOfYear());
            }
        }
        
        // Eager load absensi untuk performa (avoid N+1)
        $sesiList = $sesiQuery->with(['absensi' => function($q) use ($mahasiswaId) {
            $q->where('id_mahasiswa', $mahasiswaId);
        }])->get();

        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpha = 0;

        foreach ($sesiList as $sesi) {
            // Gunakan eager loaded absensi (sudah di-filter di query)
            $absensi = $sesi->absensi->first();

            if ($absensi) {
                switch ($absensi->status) {
                    case 'hadir':
                        $hadir++;
                        break;
                    case 'izin':
                        $izin++;
                        break;
                    case 'sakit':
                        $sakit++;
                        break;
                    case 'alpha':
                    default:
                        $alpha++;
                        break;
                }
            } else {
                // Jika tidak ada record absensi, dianggap alpha
                $alpha++;
            }
        }

        $total = $hadir + $izin + $sakit + $alpha;
        $persentaseKehadiran = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

        return [
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'total' => $total,
            'persentase_kehadiran' => $persentaseKehadiran
        ];
    }

    /**
     * Export to PDF
     */
    private function exportPDF($jadwal, $semesterId)
    {
        // Implementasi PDF export
        return response()->json(['message' => 'PDF export not implemented yet']);
    }

    /**
     * Export to Excel
     */
    private function exportExcel($jadwal, $semesterId)
    {
        // Implementasi Excel export
        return response()->json(['message' => 'Excel export not implemented yet']);
    }
}
