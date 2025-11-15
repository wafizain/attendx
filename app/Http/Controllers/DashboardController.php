<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SesiAbsensi;
use App\Models\Kelas;
use App\Models\Absensi;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'dosen':
                return $this->dosenDashboard();
            case 'mahasiswa':
                return $this->mahasiswaDashboard();
            default:
                abort(403, 'Role tidak dikenali');
        }
    }
    
    private function adminDashboard()
    {
        $today = now()->format('Y-m-d');
        
        // Statistik Dasar
        $totalAdmin = User::where('role', 'admin')->count();
        $totalDosen = User::where('role', 'dosen')->count();
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $totalKelas = Kelas::count();
        $totalMataKuliah = \App\Models\MataKuliah::count();
        
        // Sesi Absensi
        $sesiAktif = SesiAbsensi::where('status', 'aktif')->count();
        $sesiHariIni = SesiAbsensi::whereDate('tanggal', $today)->count();
        
        // Statistik Kehadiran Hari Ini
        $absensiHariIni = Absensi::whereDate('created_at', $today)->count();
        $hadirHariIni = Absensi::whereDate('created_at', $today)->where('status', 'hadir')->count();
        $izinHariIni = Absensi::whereDate('created_at', $today)->where('status', 'izin')->count();
        $sakitHariIni = Absensi::whereDate('created_at', $today)->where('status', 'sakit')->count();
        $alphaHariIni = Absensi::whereDate('created_at', $today)->where('status', 'alpha')->count();
        
        // Persentase Kehadiran Hari Ini
        $persentaseKehadiranHariIni = $absensiHariIni > 0 
            ? round(($hadirHariIni / $absensiHariIni) * 100, 1) 
            : 0;
        
        // Statistik Kehadiran Keseluruhan
        $totalAbsensi = Absensi::count();
        $totalHadir = Absensi::where('status', 'hadir')->count();
        $totalIzin = Absensi::where('status', 'izin')->count();
        $totalSakit = Absensi::where('status', 'sakit')->count();
        $totalAlpha = Absensi::where('status', 'alpha')->count();
        
        // Persentase Kehadiran Keseluruhan
        $persentaseKehadiranTotal = $totalAbsensi > 0 
            ? round(($totalHadir / $totalAbsensi) * 100, 1) 
            : 0;
        
        // Data untuk Chart - Kehadiran 7 Hari Terakhir
        $chartLabels = [];
        $chartHadir = [];
        $chartIzin = [];
        $chartSakit = [];
        $chartAlpha = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->isoFormat('DD MMM');
            
            $chartHadir[] = Absensi::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'hadir')->count();
            $chartIzin[] = Absensi::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'izin')->count();
            $chartSakit[] = Absensi::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'sakit')->count();
            $chartAlpha[] = Absensi::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'alpha')->count();
        }
        
        // Recent Sessions
        $recentSesi = SesiAbsensi::with(['kelas.mataKuliah', 'kelas.dosen'])
            ->whereHas('kelas')
            ->whereHas('kelas.mataKuliah')
            ->whereHas('kelas.dosen')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Total Mahasiswa untuk ditampilkan di welcome section
        $totalUser = $totalMahasiswa;
        
        $data = [
            // Statistik Dasar
            'totalAdmin' => $totalAdmin,
            'totalDosen' => $totalDosen,
            'totalMahasiswa' => $totalMahasiswa,
            'totalKelas' => $totalKelas,
            'totalMataKuliah' => $totalMataKuliah,
            
            // Sesi
            'sesiAktif' => $sesiAktif,
            'sesiHariIni' => $sesiHariIni,
            'recentSesi' => $recentSesi,
            
            // Kehadiran Hari Ini
            'absensiHariIni' => $absensiHariIni,
            'hadirHariIni' => $hadirHariIni,
            'izinHariIni' => $izinHariIni,
            'sakitHariIni' => $sakitHariIni,
            'alphaHariIni' => $alphaHariIni,
            'persentaseKehadiranHariIni' => $persentaseKehadiranHariIni,
            
            // Kehadiran Total
            'totalAbsensi' => $totalAbsensi,
            'totalHadir' => $totalHadir,
            'totalIzin' => $totalIzin,
            'totalSakit' => $totalSakit,
            'totalAlpha' => $totalAlpha,
            'persentaseKehadiranTotal' => $persentaseKehadiranTotal,
            
            // Chart Data
            'chartLabels' => json_encode($chartLabels),
            'chartHadir' => json_encode($chartHadir),
            'chartIzin' => json_encode($chartIzin),
            'chartSakit' => json_encode($chartSakit),
            'chartAlpha' => json_encode($chartAlpha),
            
            // Sistem
            'totalUser' => $totalUser,
        ];
        
        return view('admin.dashboard', $data);
    }
    
    private function dosenDashboard()
    {
        $dosen = auth()->user();
        $today = now()->format('Y-m-d');
        
        // Jadwal hari ini
        $jadwalHariIni = SesiAbsensi::with(['kelas.mataKuliah'])
            ->whereHas('kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            ->whereDate('tanggal', $today)
            ->orderBy('waktu_mulai', 'asc')
            ->get();
            
        // Kelas yang sedang berlangsung
        $currentTime = now();
        $kelasBerlangsung = SesiAbsensi::with(['kelas.mataKuliah'])
            ->whereHas('kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            ->where('status', 'aktif')
            ->where('started_at', '!=', null)
            ->where(function($q) use ($currentTime) {
                $q->whereNull('waktu_selesai')
                  ->orWhere('waktu_selesai', '>', $currentTime);
            })
            ->get();
            
        // Notifikasi absensi fingerprint (hasil fingerprint hari ini)
        $notifikasiFingerprint = Absensi::with(['mahasiswa' => function($q) {
                $q->select('id', 'name', 'username');
            }, 'sesiAbsensi.kelas.mataKuliah'])
            ->whereHas('sesiAbsensi.kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            ->whereDate('created_at', $today)
            ->where('metode_absen', 'fingerprint')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Statistik kehadiran hari ini
        $statistikKehadiran = [
            'hadir' => Absensi::whereHas('sesiAbsensi.kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })->whereDate('created_at', $today)->where('status', 'hadir')->count(),
            'izin' => Absensi::whereHas('sesiAbsensi.kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })->whereDate('created_at', $today)->where('status', 'izin')->count(),
            'sakit' => Absensi::whereHas('sesiAbsensi.kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })->whereDate('created_at', $today)->where('status', 'sakit')->count(),
            'alpha' => Absensi::whereHas('sesiAbsensi.kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })->whereDate('created_at', $today)->where('status', 'alpha')->count(),
        ];
        
        // Riwayat pertemuan terakhir
        $riwayatPertemuan = SesiAbsensi::with(['kelas.mataKuliah'])
            ->whereHas('kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai', 'desc')
            ->limit(5)
            ->get();
            
        // Total kelas dan sesi aktif (tetap dipertahankan)
        $totalKelas = Kelas::where('dosen_id', $dosen->id)->count();
        $sesiAktif = SesiAbsensi::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'aktif')->count();
        
        $data = [
            'totalKelas' => $totalKelas,
            'sesiAktif' => $sesiAktif,
            'jadwalHariIni' => $jadwalHariIni,
            'kelasBerlangsung' => $kelasBerlangsung,
            'notifikasiFingerprint' => $notifikasiFingerprint,
            'statistikKehadiran' => $statistikKehadiran,
            'riwayatPertemuan' => $riwayatPertemuan,
        ];
        
        return view('dosen.dashboard', $data);
    }
    
    private function mahasiswaDashboard()
    {
        $mahasiswa = auth()->user();
        
        // Get kelas yang diikuti mahasiswa
        $kelasList = Kelas::whereHas('mahasiswa', function($q) use ($mahasiswa) {
            $q->where('users.id', $mahasiswa->id);
        })->with('mataKuliah', 'dosen')->get();
        
        // Get absensi mahasiswa (gunakan kolom id_mahasiswa sesuai schema terbaru)
        $totalAbsensi = Absensi::where('id_mahasiswa', $mahasiswa->id)->count();
        $hadirCount = Absensi::where('id_mahasiswa', $mahasiswa->id)
            ->where('status', 'hadir')->count();
        $izinCount = Absensi::where('id_mahasiswa', $mahasiswa->id)
            ->where('status', 'izin')->count();
        $sakitCount = Absensi::where('id_mahasiswa', $mahasiswa->id)
            ->where('status', 'sakit')->count();
        $alphaCount = Absensi::where('id_mahasiswa', $mahasiswa->id)
            ->where('status', 'alpha')->count();
        
        $data = [
            'kelasList' => $kelasList,
            'totalKelas' => $kelasList->count(),
            'totalAbsensi' => $totalAbsensi,
            'hadirCount' => $hadirCount,
            'izinCount' => $izinCount,
            'sakitCount' => $sakitCount,
            'alphaCount' => $alphaCount,
            'persentaseKehadiran' => $totalAbsensi > 0 ? round(($hadirCount / $totalAbsensi) * 100, 2) : 0,
            'recentAbsensi' => Absensi::with(['sesiAbsensi.kelas.mataKuliah'])
                ->where('id_mahasiswa', $mahasiswa->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
        
        return view('mahasiswa.dashboard', $data);
    }
}
