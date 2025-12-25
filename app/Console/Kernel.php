<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
         // Her gün gece saat 2'de çalışsın
        //$schedule->command('photos:delete-old')
         //       ->dailyAt('02:00')
          //      ->withoutOverlapping();

        // Her gün gece yarısı çalıştır
        $schedule->command('logs:clean')->daily();
        // Her gün saat 02:00'de kapanan destek taleplerini temizle (1 ay sonra)
        $schedule->command('support:delete-old')
            ->daily()
            ->at('02:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        // Haftalık olarak çalışsın (Pazartesi saat 02:00)
        $schedule->command('photos:delete-old')
                ->weekly()
                ->mondays()
                ->at('02:00')
                ->withoutOverlapping();
        $schedule->command('calls:delete-old')
            ->daily()
            ->at('02:00')
            ->runInBackground();
            
        $schedule->command('queue:work --stop-when-empty')->everyMinute();

    }

        

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
