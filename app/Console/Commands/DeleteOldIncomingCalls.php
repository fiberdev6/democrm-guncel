<?php

namespace App\Console\Commands;

use App\Models\IncomingCall;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldIncomingCalls extends Command
{
    protected $signature = 'calls:delete-old';
    protected $description = 'Delete incoming calls older than 15 days';

    public function handle()
    {
        $date = Carbon::now()->subDays(15);
        
        $deleted = IncomingCall::where('created_at', '<', $date)
            ->delete();
            
        $this->info("Deleted {$deleted} records older than 15 days.");
        
        return Command::SUCCESS;
    }
}
