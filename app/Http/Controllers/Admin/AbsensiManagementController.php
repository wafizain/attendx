<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Storage;

class AbsensiManagementController extends Controller
{
    /**
     * Rekap absensi harian
     */
    public function rekapHarian(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        
        $sesiList = SesiAbsensi::with(['kelas.mataKuliah', 'kelas.dosen'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('waktu_mulai')
            ->get();

        $statistik = [];
        foreach ($sesiList as $sesi) {
            $statistik[$sesi->id] = [
                'hadir' => $sesi->absensi()->where('status', 'hadir')->count(),
                'pending' => $sesi->absensi()->where('status', 'pending')->count(),
                'izin' => $sesi->absensi()->where('status', 'izin')->count(),
                'sakit' => $sesi->absensi()->where('status', 'sakit')->count(),
                'alpha' => $sesi->absensi()->where('status', 'alpha')->count(),
                'total' => $sesi->absensi()->count(),
            ];
        }

        return view('admin.absensi.rekap-harian', compact('sesiList', 'statistik', 'tanggal'));
    }

    /**
     * Rekap per kelas/matkul
     */
    public function rekapKelas(Request $request)
    {
        $kelasId = $request->input('kelas_id');
        $kelasList = Kelas::with('mataKuliah')->aktif()->get();

        $data = [];
        if ($kelasId) {
            $kelas = Kelas::with(['mataKuliah', 'mahasiswa', 'sesiAbsensi.absensi'])->findOrFail($kelasId);
            
            // Hitung statistik per mahasiswa
            foreach ($kelas->mahasiswa as $mahasiswa) {
                $data[$mahasiswa->id] = [
                    'mahasiswa' => $mahasiswa,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpha' => 0,
                    'total_pertemuan' => $kelas->sesiAbsensi->count(),
                ];

                foreach ($kelas->sesiAbsensi as $sesi) {
                    $absensi = $sesi->absensi->where('mahasiswa_id', $mahasiswa->id)->first();
                    if ($absensi) {
                        $data[$mahasiswa->id][$absensi->status]++;
                    }
                }

                // Hitung persentase
                if ($data[$mahasiswa->id]['total_pertemuan'] > 0) {
                    $data[$mahasiswa->id]['persentase'] = round(
                        ($data[$mahasiswa->id]['hadir'] / $data[$mahasiswa->id]['total_pertemuan']) * 100,
                        2
                    );
                } else {
                    $data[$mahasiswa->id]['persentase'] = 0;
                }
            }
        }

        return view('admin.absensi.rekap-kelas', compact('kelasList', 'data', 'kelasId'));
    }

    /**
     * Koreksi/manajemen entri absensi
     */
    public function koreksi(Request $request)
    {
        $query = Absensi::with(['mahasiswa', 'sesiAbsensi.kelas.mataKuliah']);

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', $request->tanggal);
            });
        }

        // Filter by mahasiswa
        if ($request->filled('mahasiswa_id')) {
            $query->where('mahasiswa_id', $request->mahasiswa_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensiList = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.absensi.koreksi', compact('absensiList'));
    }

    /**
     * Update absensi (koreksi)
     */
    public function updateKoreksi(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $oldStatus = $absensi->status;
        $absensi->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        LogHelper::update(
            auth()->id(), 
            'absensi', 
            "Koreksi absensi {$absensi->mahasiswa->name}: {$oldStatus} → {$request->status}"
        );

        return redirect()->back()->with('success', 'Absensi berhasil dikoreksi.');
    }

    /**
     * Foto capture gallery
     */
    public function foto(Request $request)
    {
        $query = Absensi::with(['mahasiswa', 'sesiAbsensi.kelas.mataKuliah'])
            ->whereNotNull('foto_absensi');

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', $request->tanggal);
            });
        }

        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $fotos = $query->orderBy('waktu_absen', 'desc')->paginate(24);
        $kelasList = Kelas::with('mataKuliah')->aktif()->get();

        return view('admin.absensi.foto', compact('fotos', 'kelasList'));
    }

    /**
     * Delete foto absensi
     */
    public function deleteFoto($id)
    {
        $absensi = Absensi::findOrFail($id);
        
        if ($absensi->foto_absensi && Storage::disk('public')->exists($absensi->foto_absensi)) {
            Storage::disk('public')->delete($absensi->foto_absensi);
        }

        $absensi->update(['foto_absensi' => null]);

        LogHelper::delete(auth()->id(), 'absensi', "Menghapus foto absensi {$absensi->mahasiswa->name}");

        return redirect()->back()->with('success', 'Foto absensi berhasil dihapus.');
    }
}
