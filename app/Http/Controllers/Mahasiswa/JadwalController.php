<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    /**
     * Tampilkan jadwal kuliah mahasiswa
     */
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();
        $today = Carbon::today();
        
        // Ambil jadwal kuliah yang diikuti mahasiswa
        $query = JadwalKuliah::with(['mataKuliah', 'dosen', 'kelas', 'ruangan'])
            ->whereHas('mahasiswa', function($q) use ($mahasiswa) {
                $q->where('mahasiswa.id_user', $mahasiswa->id);
            })
            ->where('status', 'aktif');
        
        // Filter by hari jika ada
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }
        
        $jadwalList = $query->orderBy('hari', 'asc')
                           ->orderBy('jam_mulai', 'asc')
                           ->get();
        
        // Mapping hari untuk display
        $hariMapping = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        
        // Get hari ini (1-7)
        $todayHariNumber = $today->dayOfWeek == 0 ? 7 : $today->dayOfWeek;
        
        // Pisahkan jadwal hari ini dan jadwal lainnya
        $jadwalHariIni = $jadwalList->filter(function($jadwal) use ($todayHariNumber) {
            return $jadwal->hari == $todayHariNumber;
        })->values();
        
        $jadwalLainnya = $jadwalList->filter(function($jadwal) use ($todayHariNumber) {
            return $jadwal->hari != $todayHariNumber;
        })->values();
        
        // Group jadwal lainnya by hari
        $jadwalByHari = $jadwalLainnya->groupBy('hari');
        
        return view('mahasiswa.jadwal.index', compact(
            'jadwalHariIni', 
            'jadwalByHari', 
            'hariMapping',
            'todayHariNumber'
        ));
    }
    
    /**
     * Tampilkan detail jadwal kuliah
     */
    public function show($id)
    {
        $mahasiswa = auth()->user();
        
        // Ambil jadwal dengan validasi mahasiswa terdaftar
        $jadwal = JadwalKuliah::with(['mataKuliah', 'dosen', 'kelas', 'ruangan', 'mahasiswa'])
            ->whereHas('mahasiswa', function($q) use ($mahasiswa) {
                $q->where('mahasiswa.id_user', $mahasiswa->id);
            })
            ->findOrFail($id);
        
        // Ambil pertemuan untuk jadwal ini
        $pertemuanList = Pertemuan::where('id_jadwal', $jadwal->id)
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();
        
        // Hitung statistik kehadiran mahasiswa di jadwal ini
        $totalPertemuan = Pertemuan::where('id_jadwal', $jadwal->id)
            ->where('status_sesi', 'selesai')
            ->count();
        
        $statistik = [
            'total_pertemuan' => $totalPertemuan,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];
        
        // Hitung kehadiran mahasiswa
        if ($totalPertemuan > 0) {
            $absensiMahasiswa = \DB::table('sesi_absensi')
                ->join('absensi', 'sesi_absensi.id', '=', 'absensi.sesi_absensi_id')
                ->where('sesi_absensi.kelas_id', $jadwal->id_kelas)
                ->where('sesi_absensi.status', 'selesai')
                ->where('absensi.id_mahasiswa', $mahasiswa->id)
                ->select('absensi.status', \DB::raw('count(*) as total'))
                ->groupBy('absensi.status')
                ->pluck('total', 'status');
            
            $statistik['hadir'] = $absensiMahasiswa['hadir'] ?? 0;
            $statistik['izin'] = $absensiMahasiswa['izin'] ?? 0;
            $statistik['sakit'] = $absensiMahasiswa['sakit'] ?? 0;
            $statistik['alpha'] = $absensiMahasiswa['alpha'] ?? 0;
        }
        
        $statistik['persentase_kehadiran'] = $totalPertemuan > 0 
            ? round(($statistik['hadir'] / $totalPertemuan) * 100, 2) 
            : 0;
        
        return view('mahasiswa.jadwal.show', compact('jadwal', 'pertemuanList', 'statistik'));
    }
}
