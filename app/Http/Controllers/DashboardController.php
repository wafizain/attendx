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
        $data = [
            'totalAdmin' => User::where('role', 'admin')->count(),
            'totalDosen' => User::where('role', 'dosen')->count(),
            'totalMahasiswa' => User::where('role', 'mahasiswa')->count(),
            'totalKelas' => Kelas::count(),
            'sesiAktif' => SesiAbsensi::where('status', 'aktif')->count(),
            'recentSesi' => SesiAbsensi::with(['kelas.mataKuliah', 'kelas.dosen'])
                ->whereHas('kelas')
                ->whereHas('kelas.mataKuliah')
                ->whereHas('kelas.dosen')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
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
