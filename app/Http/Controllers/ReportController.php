<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\User;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display report dashboard
     */
    public function index()
    {
        $mataKuliah = MataKuliah::where('status', 1)->orderBy('nama_mk')->get();
        $kelas = Kelas::with(['mataKuliah', 'dosen'])->where('status', 'aktif')->orderBy('nama_kelas')->get();
        
        return view('admin.reports.index', compact('mataKuliah', 'kelas'));
    }

    /**
     * Generate report by class
     */
    public function byClass(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:hadir,izin,sakit,alpha'
        ]);

        $kelas = Kelas::with(['mataKuliah', 'dosen'])->findOrFail($validated['kelas_id']);
        
        // Get sesi absensi
        $query = SesiAbsensi::where('kelas_id', $kelas->id)
            ->with(['absensi.mahasiswa']);

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $sesiList = $query->orderBy('tanggal', 'asc')->get();

        // Get all mahasiswa in class
        $mahasiswaList = $kelas->mahasiswa;

        // Build report data
        $reportData = [];
        foreach ($mahasiswaList as $mahasiswa) {
            $data = [
                'mahasiswa' => $mahasiswa,
                'total_sesi' => $sesiList->count(),
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
                'persentase' => 0,
                'detail' => []
            ];

            foreach ($sesiList as $sesi) {
                $absensi = $sesi->absensi->where('id_mahasiswa', $mahasiswa->id)->first();
                $status = $absensi ? $absensi->status : 'alpha';
                
                $data['detail'][] = [
                    'sesi' => $sesi,
                    'status' => $status,
                    'waktu' => $absensi ? $absensi->waktu_absen : null
                ];

                $data[$status]++;
            }

            if ($data['total_sesi'] > 0) {
                $data['persentase'] = round(($data['hadir'] / $data['total_sesi']) * 100, 2);
            }

            // Filter by status if requested
            if ($request->filled('status')) {
                if ($data[$request->status] > 0) {
                    $reportData[] = $data;
                }
            } else {
                $reportData[] = $data;
            }
        }

        LogHelper::view(auth()->id(), 'Laporan', 'Melihat laporan kelas: ' . $kelas->nama_kelas);

        return view('admin.reports.by-class', compact('kelas', 'sesiList', 'reportData', 'request'));
    }

    /**
     * Generate report by student
     */
    public function byStudent(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id'
        ]);

        $mahasiswa = User::where('role', 'mahasiswa')->findOrFail($validated['mahasiswa_id']);
        
        // Get absensi dengan kolom yang benar (id_mahasiswa)
        $query = Absensi::where('id_mahasiswa', $mahasiswa->id)
            ->with(['sesiAbsensi.kelas.mataKuliah', 'sesiAbsensi.kelas.dosen']);

        if ($request->filled('start_date')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', '>=', $request->start_date);
            });
        }

        if ($request->filled('end_date')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', '<=', $request->end_date);
            });
        }

        if ($request->filled('mata_kuliah_id')) {
            $query->whereHas('sesiAbsensi.kelas', function($q) use ($request) {
                $q->where('mata_kuliah_id', $request->mata_kuliah_id);
            });
        }

        $absensiList = $query->orderBy('created_at', 'desc')->get();

        // Group by kelas
        $reportByKelas = [];
        foreach ($absensiList as $absensi) {
            if (!$absensi->sesiAbsensi || !$absensi->sesiAbsensi->kelas) {
                continue;
            }
            
            $kelasId = $absensi->sesiAbsensi->kelas_id;
            if (!isset($reportByKelas[$kelasId])) {
                $reportByKelas[$kelasId] = [
                    'kelas' => $absensi->sesiAbsensi->kelas,
                    'total' => 0,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpha' => 0,
                    'persentase' => 0
                ];
            }
            $reportByKelas[$kelasId]['total']++;
            $reportByKelas[$kelasId][$absensi->status]++;
        }

        // Calculate percentage
        foreach ($reportByKelas as $kelasId => $data) {
            if ($data['total'] > 0) {
                $reportByKelas[$kelasId]['persentase'] = round(($data['hadir'] / $data['total']) * 100, 2);
            }
        }

        LogHelper::view(auth()->id(), 'Laporan', 'Melihat laporan mahasiswa: ' . $mahasiswa->name);

        return view('admin.reports.by-student', compact('mahasiswa', 'absensiList', 'reportByKelas', 'request'));
    }

    /**
     * Export to CSV - By Class
     */
    public function exportClassCSV(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ]);

        $kelas = Kelas::with(['mataKuliah', 'dosen'])->findOrFail($validated['kelas_id']);
        
        $query = SesiAbsensi::where('kelas_id', $kelas->id)->with(['absensi.mahasiswa']);

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $sesiList = $query->orderBy('tanggal', 'asc')->get();
        $mahasiswaList = $kelas->mahasiswa;

        $filename = 'laporan_kelas_' . $kelas->nama_kelas . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($kelas, $sesiList, $mahasiswaList) {
            $file = fopen('php://output', 'w');
            
            // Header info
            fputcsv($file, ['LAPORAN ABSENSI KELAS']);
            fputcsv($file, ['Mata Kuliah', $kelas->mataKuliah->nama_mk]);
            fputcsv($file, ['Kelas', $kelas->nama_kelas]);
            fputcsv($file, ['Dosen', $kelas->dosen->name]);
            fputcsv($file, ['Tahun Ajaran', $kelas->tahun_ajaran]);
            fputcsv($file, []);

            // Table header
            $header = ['No', 'NIM', 'Nama Mahasiswa'];
            foreach ($sesiList as $sesi) {
                $header[] = $sesi->tanggal->format('d/m/Y');
            }
            $header[] = 'Total Hadir';
            $header[] = 'Total Izin';
            $header[] = 'Total Sakit';
            $header[] = 'Total Alpha';
            $header[] = 'Persentase';
            fputcsv($file, $header);

            // Data rows
            $no = 1;
            foreach ($mahasiswaList as $mahasiswa) {
                $row = [$no++, $mahasiswa->no_induk ?? '-', $mahasiswa->name];
                
                $hadir = 0;
                $izin = 0;
                $sakit = 0;
                $alpha = 0;

                foreach ($sesiList as $sesi) {
                    $absensi = $sesi->absensi->where('id_mahasiswa', $mahasiswa->id)->first();
                    $status = $absensi ? strtoupper(substr($absensi->status, 0, 1)) : 'A';
                    $row[] = $status;

                    if ($absensi) {
                        ${$absensi->status}++;
                    } else {
                        $alpha++;
                    }
                }

                $total = $sesiList->count();
                $persentase = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

                $row[] = $hadir;
                $row[] = $izin;
                $row[] = $sakit;
                $row[] = $alpha;
                $row[] = $persentase . '%';

                fputcsv($file, $row);
            }

            fclose($file);
        };

        LogHelper::view(auth()->id(), 'Laporan', 'Export CSV laporan kelas: ' . $kelas->nama_kelas);

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to CSV - By Student
     */
    public function exportStudentCSV(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ]);

        $mahasiswa = User::where('role', 'mahasiswa')->findOrFail($validated['mahasiswa_id']);
        
        $query = Absensi::where('id_mahasiswa', $mahasiswa->id)
            ->with(['sesiAbsensi.kelas.mataKuliah']);

        if ($request->filled('start_date')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('sesiAbsensi', function($q) use ($request) {
                $q->whereDate('tanggal', '<=', $request->end_date);
            });
        }

        $absensiList = $query->orderBy('created_at', 'desc')->get();

        $filename = 'laporan_mahasiswa_' . $mahasiswa->name . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($mahasiswa, $absensiList) {
            $file = fopen('php://output', 'w');
            
            // Header info
            fputcsv($file, ['LAPORAN ABSENSI MAHASISWA']);
            fputcsv($file, ['Nama', $mahasiswa->name]);
            fputcsv($file, ['NIM', $mahasiswa->no_induk ?? '-']);
            fputcsv($file, ['Email', $mahasiswa->email]);
            fputcsv($file, []);

            // Table header
            fputcsv($file, ['No', 'Tanggal', 'Mata Kuliah', 'Kelas', 'Topik', 'Status', 'Waktu Absen']);

            // Data rows
            $no = 1;
            foreach ($absensiList as $absensi) {
                fputcsv($file, [
                    $no++,
                    $absensi->sesiAbsensi->tanggal->format('d/m/Y'),
                    $absensi->sesiAbsensi->kelas->mataKuliah->nama_mk,
                    $absensi->sesiAbsensi->kelas->nama_kelas,
                    $absensi->sesiAbsensi->topik,
                    ucfirst($absensi->status),
                    $absensi->waktu_absen ? $absensi->waktu_absen->format('H:i:s') : '-'
                ]);
            }

            fclose($file);
        };

        LogHelper::view(auth()->id(), 'Laporan', 'Export CSV laporan mahasiswa: ' . $mahasiswa->name);

        return response()->stream($callback, 200, $headers);
    }
}
