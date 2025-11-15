<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pertemuan;
use App\Models\JadwalKuliah;
use Carbon\Carbon;

class PertemuanController extends Controller
{
    /**
     * Display listing of pertemuan for dosen
     */
    public function index(Request $request)
    {
        $dosen = Auth::user();
        
        // Get jadwal kuliah yang diampu dosen
        $jadwalList = JadwalKuliah::with(['mataKuliah', 'kelas'])
            ->where('id_dosen', $dosen->id)
            ->where('status', 'aktif')
            ->get();

        // Get pertemuan for all jadwal
        $query = Pertemuan::with(['jadwal.mataKuliah', 'jadwal.kelas', 'ruangan', 'pembuka'])
            ->whereHas('jadwal', function($q) use ($dosen) {
                $q->where('id_dosen', $dosen->id);
            });

        // Filter by jadwal
        if ($request->filled('jadwal_id')) {
            $query->where('id_jadwal', $request->jadwal_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        $pertemuanList = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->paginate(20);

        return view('dosen.pertemuan.index', compact('pertemuanList', 'jadwalList'));
    }

    /**
     * Display detail of pertemuan
     */
    public function show($id)
    {
        $dosen = Auth::user();
        
        $pertemuan = Pertemuan::with([
            'jadwal.mataKuliah', 
            'jadwal.kelas', 
            'jadwal.dosen',
            'ruangan',
            'pembuka',
            'absensi.mahasiswa'
        ])
        ->where('id', $id)
        ->whereHas('jadwal', function($q) use ($dosen) {
            $q->where('id_dosen', $dosen->id);
        })
        ->firstOrFail();

        // Get statistics
        $totalMahasiswa = $pertemuan->jadwal ? $pertemuan->jadwal->mahasiswa()->count() : 0;
        $hadirCount = $pertemuan->absensi->where('status', 'hadir')->count();
        $izinCount = $pertemuan->absensi->where('status', 'izin')->count();
        $sakitCount = $pertemuan->absensi->where('status', 'sakit')->count();
        $alpaCount = $pertemuan->absensi->where('status', 'alpa')->count();

        return view('dosen.pertemuan.show', compact(
            'pertemuan', 
            'totalMahasiswa', 
            'hadirCount', 
            'izinCount', 
            'sakitCount', 
            'alpaCount'
        ));
    }
}
