<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pertemuan;
use App\Notifications\DailyReportNotification;
use Carbon\Carbon;

class GenerateDailyReport extends Command
{
    protected $signature = 'pertemuan:daily-report';
    protected $description = 'Generate and send daily attendance report';

    public function handle()
    {
        $today = Carbon::today();
        
        $pertemuans = Pertemuan::with(['jadwal.mataKuliah', 'jadwal.dosen', 'absensi'])
            ->whereDate('tanggal', $today)
            ->whereIn('status_sesi', ['selesai', 'berjalan'])
            ->get();

        if ($pertemuans->isEmpty()) {
            $this->info("No pertemuan today");
            return 0;
        }

        $report = [
            'date' => $today->format('d/m/Y'),
            'total_pertemuan' => $pertemuans->count(),
            'pertemuans' => [],
        ];

        foreach ($pertemuans as $p) {
            $stats = $p->getStatistikKehadiran();
            $report['pertemuans'][] = [
                'mata_kuliah' => $p->jadwal->mataKuliah->nama_mk,
                'dosen' => $p->jadwal->dosen->name,
                'minggu_ke' => $p->minggu_ke,
                'statistik' => $stats,
            ];
        }

        // Send to admin
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            // $admin->notify(new DailyReportNotification($report));
        }

        $this->info("Daily report generated for {$pertemuans->count()} pertemuan");
        
        return 0;
    }
}
