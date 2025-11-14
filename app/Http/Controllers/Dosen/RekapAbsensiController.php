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
        
        // Get pertemuan dalam semester - fallback to all pertemuan if no semester_id column
        $pertemuanQuery = Pertemuan::where('id_jadwal', $jadwal->id)
            ->where('status_sesi', 'selesai');
            
        // Only filter by semester if the relationship exists
        if (Schema::hasColumn('pertemuan', 'semester_id')) {
            $pertemuanQuery->where('semester_id', $semesterId);
        }
        
        $pertemuanList = $pertemuanQuery->orderBy('tanggal', 'asc')->get();

        // Get rekap per mahasiswa
        $rekapMahasiswa = [];
        foreach ($jadwal->mahasiswa as $mahasiswa) {
            $rekap = $this->getRekapAbsensiPerMahasiswa($mahasiswa->id, $jadwal->id, $semesterId);
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
        // Get pertemuan dalam semester - fallback to all pertemuan if no semester_id column
        $pertemuanQuery = Pertemuan::where('id_jadwal', $jadwal->id)
            ->where('status_sesi', 'selesai');
            
        // Only filter by semester if the relationship exists
        if (Schema::hasColumn('pertemuan', 'semester_id')) {
            $pertemuanQuery->where('semester_id', $semesterId);
        }
        
        $pertemuanList = $pertemuanQuery->get();

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
            $statMahasiswa = $this->getRekapAbsensiPerMahasiswa($mahasiswa->id, $jadwal->id, $semesterId);
            
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
        // Get pertemuan dalam semester - fallback to all pertemuan if no semester_id column
        $pertemuanQuery = Pertemuan::where('id_jadwal', $jadwalId)
            ->where('status_sesi', 'selesai');
            
        // Only filter by semester if the relationship exists
        if (Schema::hasColumn('pertemuan', 'semester_id')) {
            $pertemuanQuery->where('semester_id', $semesterId);
        }
        
        $pertemuanList = $pertemuanQuery->get();

        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpha = 0;

        foreach ($pertemuanList as $pertemuan) {
            if ($pertemuan->sesiAbsensi) {
                $absensi = $pertemuan->sesiAbsensi->absensi()
                    ->where('mahasiswa_id', $mahasiswaId)
                    ->first();

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
                        default:
                            $alpha++;
                            break;
                    }
                } else {
                    $alpha++;
                }
            } else {
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
