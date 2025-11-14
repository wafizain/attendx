<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pertemuan;
use Carbon\Carbon;

class AutoClosePertemuan extends Command
{
    protected $signature = 'pertemuan:auto-close';
    protected $description = 'Auto-close pertemuan yang sudah lewat waktu';

    public function handle()
    {
        $now = Carbon::now();
        
        $pertemuans = Pertemuan::where('status_sesi', 'berjalan')
            ->whereDate('tanggal', '<=', $now->toDateString())
            ->get();

        $closed = 0;
        
        foreach ($pertemuans as $pertemuan) {
            $window = $pertemuan->getAbsensiWindow();
            
            if ($now->gt($window['close'])) {
                $pertemuan->closeSession(null, false);
                $closed++;
                $this->info("Closed: {$pertemuan->jadwal->mataKuliah->nama_mk} - Pertemuan {$pertemuan->minggu_ke}");
            }
        }

        $this->info("Total closed: {$closed} pertemuan");
        
        return 0;
    }
}
