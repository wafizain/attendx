<?php

namespace App\Services;

use App\Models\Pertemuan;
use Illuminate\Support\Facades\Response;

class PertemuanExportService
{
    /**
     * Export to CSV
     */
    public function exportCSV(Pertemuan $pertemuan)
    {
        $filename = 'pertemuan_' . $pertemuan->id . '_' . date('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pertemuan) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['NIM', 'Nama', 'Waktu Scan', 'Status', 'Device', 'Confidence']);
            
            // Data
            foreach ($pertemuan->absensi as $a) {
                fputcsv($file, [
                    $a->mahasiswa->nim,
                    $a->mahasiswa->nama,
                    $a->waktu_scan ? $a->waktu_scan->format('Y-m-d H:i:s') : '-',
                    $a->status,
                    $a->device_id ?? '-',
                    $a->confidence ?? '-',
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (requires maatwebsite/excel)
     */
    public function exportExcel(Pertemuan $pertemuan)
    {
        // Install: composer require maatwebsite/excel
        // return Excel::download(new PertemuanExport($pertemuan), 'pertemuan.xlsx');
        
        // For now, return CSV
        return $this->exportCSV($pertemuan);
    }

    /**
     * Export to PDF (requires barryvdh/laravel-dompdf)
     */
    public function exportPDF(Pertemuan $pertemuan)
    {
        // Install: composer require barryvdh/laravel-dompdf
        // $pdf = PDF::loadView('pertemuan.pdf', compact('pertemuan'));
        // return $pdf->download('pertemuan.pdf');
        
        $data = [
            'pertemuan' => $pertemuan,
            'statistik' => $pertemuan->getStatistikKehadiran(),
        ];
        
        return view('pertemuan.pdf', $data);
    }
}
