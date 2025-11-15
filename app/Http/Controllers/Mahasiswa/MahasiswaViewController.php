<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Absensi;
use Illuminate\Http\Request;

class MahasiswaViewController extends Controller
{
    /**
     * Tampilkan kelas yang diikuti mahasiswa
     */
    public function kelas()
    {
        $mahasiswa = auth()->user();
        
        $kelasList = Kelas::whereHas('mahasiswa', function($q) use ($mahasiswa) {
            $q->where('mahasiswa_id', $mahasiswa->id)
              ->where('kelas_mahasiswa.status', 'aktif');
        })->with(['mataKuliah', 'dosen'])->get();
        
        return view('mahasiswa.kelas.index', compact('kelasList'));
    }
    
    /**
     * Tampilkan riwayat absensi mahasiswa
     */
    public function absensi(Request $request)
    {
        $mahasiswa = auth()->user();
        
        $query = Absensi::with(['sesiAbsensi.kelas.mataKuliah'])
            ->where('id_mahasiswa', $mahasiswa->id);
        
        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('tanggal_mulai')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', '>=', $request->tanggal_mulai);
            });
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', '<=', $request->tanggal_selesai);
            });
        }
        
        $absensiList = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get kelas list for filter
        $kelasList = Kelas::whereHas('mahasiswa', function($q) use ($mahasiswa) {
            $q->where('mahasiswa_id', $mahasiswa->id);
        })->with('mataKuliah')->get();
        
        // Statistics
        $totalAbsensi = Absensi::where('id_mahasiswa', $mahasiswa->id)->count();
        $hadirCount = Absensi::where('id_mahasiswa', $mahasiswa->id)->where('status', 'hadir')->count();
        $izinCount = Absensi::where('id_mahasiswa', $mahasiswa->id)->where('status', 'izin')->count();
        $sakitCount = Absensi::where('id_mahasiswa', $mahasiswa->id)->where('status', 'sakit')->count();
        $alphaCount = Absensi::where('id_mahasiswa', $mahasiswa->id)->where('status', 'alpha')->count();
        
        $stats = [
            'total' => $totalAbsensi,
            'hadir' => $hadirCount,
            'izin' => $izinCount,
            'sakit' => $sakitCount,
            'alpha' => $alphaCount,
            'persentase' => $totalAbsensi > 0 ? round(($hadirCount / $totalAbsensi) * 100, 2) : 0,
        ];
        
        return view('mahasiswa.absensi', compact('absensiList', 'kelasList', 'stats'));
    }
}
