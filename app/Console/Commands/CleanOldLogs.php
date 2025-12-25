<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use Carbon\Carbon;

class CleanOldLogs extends Command
{
    protected $signature = 'logs:clean';
    protected $description = 'Clean activity logs older than 7 days';

    public function handle()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $deletedCount = ActivityLog::where('created_at', '<', $sevenDaysAgo)->delete();
        
        $this->info("Deleted {$deletedCount} old log records.");
        
        return Command::SUCCESS;
    }
}