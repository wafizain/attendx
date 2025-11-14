<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\SesiAbsensi;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;

class AbsenManualController extends Controller
{
    /**
     * Display form untuk absen manual
     */
    public function index()
    {
        $dosen = auth()->user();
        
        // Get kelas yang diajar oleh dosen
        $kelasList = Kelas::with('mataKuliah')
            ->where('dosen_id', $dosen->id)
            ->where('status', 'aktif')
            ->get();
        
        return view('dosen.absen-manual.index', compact('kelasList'));
    }
    
    /**
     * Get sesi absensi berdasarkan kelas
     */
    public function getSesiByKelas(Request $request)
    {
        $kelasId = $request->kelas_id;
        $dosen = auth()->user();
        
        // Validasi bahwa kelas ini milik dosen yang login
        $kelas = Kelas::where('id', $kelasId)
            ->where('dosen_id', $dosen->id)
            ->first();
            
        if (!$kelas) {
            return response()->json(['error' => 'Kelas tidak ditemukan'], 404);
        }
        
        $sesiList = SesiAbsensi::where('kelas_id', $kelasId)
            ->where('status', 'aktif')
            ->orderBy('tanggal', 'desc')
            ->get();
        
        return response()->json($sesiList);
    }
    
    /**
     * Get mahasiswa dan status absensi
     */
    public function getMahasiswaBySesi(Request $request)
    {
        $sesiId = $request->sesi_id;
        $dosen = auth()->user();
        
        // Validasi bahwa sesi ini milik kelas dosen yang login
        $sesi = SesiAbsensi::with('kelas')
            ->whereHas('kelas', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            ->findOrFail($sesiId);
        
        $mahasiswaList = $sesi->kelas->mahasiswa()
            ->wherePivot('status', 'aktif')
            ->get();
        
        $data = [];
        foreach ($mahasiswaList as $mahasiswa) {
            $absensi = Absensi::where('sesi_absensi_id', $sesiId)
                ->where('mahasiswa_id', $mahasiswa->id)
                ->first();
            
            $data[] = [
                'id' => $mahasiswa->id,
                'no_induk' => $mahasiswa->no_induk,
                'name' => $mahasiswa->name,
                'status' => $absensi ? $absensi->status : 'alpha',
                'waktu_absen' => $absensi && $absensi->waktu_absen ? $absensi->waktu_absen : null,
                'keterangan' => $absensi ? $absensi->keterangan : null,
            ];
        }
        
        return response()->json($data);
    }
    
    /**
     * Update absensi manual
     */
    public function updateAbsensi(Request $request)
    {
        $request->validate([
            'sesi_id' => 'required|exists:sesi_absensi,id',
            'mahasiswa_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);
        
        $dosen = auth()->user();
        
        // Validasi bahwa sesi ini milik kelas dosen yang login
        $sesi = SesiAbsensi::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->findOrFail($request->sesi_id);
        
        $absensi = Absensi::where('sesi_absensi_id', $request->sesi_id)
            ->where('mahasiswa_id', $request->mahasiswa_id)
            ->first();
        
        if (!$absensi) {
            $absensi = new Absensi();
            $absensi->sesi_absensi_id = $request->sesi_id;
            $absensi->mahasiswa_id = $request->mahasiswa_id;
        }
        
        $absensi->status = $request->status;
        $absensi->keterangan = $request->keterangan;
        $absensi->metode_absen = 'manual';
        
        if ($request->status === 'hadir' && !$absensi->waktu_absen) {
            $absensi->waktu_absen = now();
        }
        
        $absensi->save();
        
        // Log aktivitas
        LogHelper::update('absensi', 'Dosen melakukan absen manual untuk mahasiswa', [
            'sesi_absensi_id' => $request->sesi_id,
            'mahasiswa_id' => $request->mahasiswa_id,
            'status' => $request->status,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil diupdate',
            'data' => $absensi
        ]);
    }
}
