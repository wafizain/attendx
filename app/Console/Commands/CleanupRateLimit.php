<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScanRateLimit;
use Carbon\Carbon;

class CleanupRateLimit extends Command
{
    protected $signature = 'pertemuan:cleanup-rate-limit';
    protected $description = 'Cleanup old rate limit data';

    public function handle()
    {
        $deleted = ScanRateLimit::where('last_scan_at', '<', Carbon::now()->subDay())
            ->delete();

        $this->info("Deleted {$deleted} old rate limit records");
        
        return 0;
    }
}
