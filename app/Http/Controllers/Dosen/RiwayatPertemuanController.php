<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Models\Absensi;
use App\Models\JadwalKuliah;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiwayatPertemuanController extends Controller
{
    public function __construct()
    {
        // Middleware ditangani di routes
    }

    /**
     * Display riwayat pertemuan dosen
     */
    public function index(Request $request)
    {
        $dosen = Auth::user();
        
        // Get pertemuan yang sudah selesai
        $query = Pertemuan::with(['jadwal.mataKuliah', 'jadwal.kelas', 'jadwal.ruangan', 'absensi.mahasiswa'])
            ->whereHas('jadwal', function($q) use ($dosen) {
                $q->where('id_dosen', $dosen->id);
            })
            ->whereIn('status_sesi', ['selesai', 'dibatalkan'])
            ->orderBy('tanggal', 'desc');

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        // Filter berdasarkan mata kuliah
        if ($request->filled('mata_kuliah')) {
            $query->whereHas('jadwal.mataKuliah', function($q) use ($request) {
                $q->where('nama_mk', 'like', '%' . $request->mata_kuliah . '%');
            });
        }

        // Filter berdasarkan kelas
        if ($request->filled('kelas')) {
            $query->whereHas('jadwal.kelas', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->kelas . '%');
            });
        }

        $pertemuanList = $query->paginate(10);

        // Get mata kuliah dan kelas untuk filter dropdown
        $mataKuliahList = JadwalKuliah::where('id_dosen', $dosen->id)
            ->where('status', 'aktif')
            ->with('mataKuliah')
            ->get()
            ->pluck('mataKuliah.nama_mk')
            ->unique()
            ->sort();

        $kelasList = JadwalKuliah::where('id_dosen', $dosen->id)
            ->where('status', 'aktif')
            ->with('kelas')
            ->get()
            ->pluck('kelas.nama')
            ->unique()
            ->sort();

        return view('dosen.riwayat-pertemuan.index', compact('pertemuanList', 'mataKuliahList', 'kelasList'));
    }

    /**
     * Display detail pertemuan
     */
    public function show($id)
    {
        $dosen = Auth::user();
        
        $pertemuan = Pertemuan::with([
            'jadwal.mataKuliah', 
            'jadwal.kelas', 
            'jadwal.ruangan',
            'absensi.mahasiswa'
        ])
        ->whereHas('jadwal', function($q) use ($dosen) {
            $q->where('id_dosen', $dosen->id);
        })
        ->findOrFail($id);

        // Get statistik detail
        $stats = [];
        $absensiList = $pertemuan->absensi;
        if ($absensiList && $absensiList->count() > 0) {
            $stats = [
                'hadir_fingerprint' => $absensiList->where('status', 'hadir')->where('verification_method', 'fingerprint')->count(),
                'hadir_manual' => $absensiList->where('status', 'hadir')->where('verification_method', 'manual')->count(),
                'hadir' => $absensiList->where('status', 'hadir')->count(),
                'izin' => $absensiList->where('status', 'izin')->count(),
                'sakit' => $absensiList->where('status', 'sakit')->count(),
                'alpha' => $absensiList->where('status', 'alpha')->count(),
                'total' => $absensiList->count(),
            ];

            // Hitung persentase kehadiran
            if ($stats['total'] > 0) {
                $stats['persentase_kehadiran'] = round(($stats['hadir'] / $stats['total']) * 100, 2);
            } else {
                $stats['persentase_kehadiran'] = 0;
            }
        }

        return view('dosen.riwayat-pertemuan.show', compact('pertemuan', 'stats'));
    }

    /**
     * Download laporan pertemuan
     */
    public function download($id, $format = 'pdf')
    {
        $dosen = Auth::user();
        
        $pertemuan = Pertemuan::with([
            'jadwal.mataKuliah', 
            'jadwal.kelas', 
            'jadwal.ruangan',
            'sesiAbsensi.absensi.mahasiswa'
        ])
        ->whereHas('jadwal', function($q) use ($dosen) {
            $q->where('id_dosen', $dosen->id);
        })
        ->findOrFail($id);

        if ($format === 'excel') {
            return $this->downloadExcel($pertemuan);
        } else {
            return $this->downloadPDF($pertemuan);
        }
    }

    /**
     * Download PDF
     */
    private function downloadPDF($pertemuan)
    {
        // Implementasi PDF download
        // Bisa menggunakan DomPDF atau library lain
        return response()->json(['message' => 'PDF download not implemented yet']);
    }

    /**
     * Download Excel
     */
    private function downloadExcel($pertemuan)
    {
        // Implementasi Excel download
        // Bisa menggunakan Laravel Excel
        return response()->json(['message' => 'Excel download not implemented yet']);
    }
}
