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
        // SEND SUBMISSION REMINDER - STUDENT
        $schedule->command('submission:reminder')->dailyAt('08:00');

        // UPDATE SUBMISSION STATUS - OVERDUE OR PENDING
        $schedule->command('submission:update-status')->hourly();

        // Audit retention policy: keep exactly the most recent three months.
        $schedule->call(function () {
            if (!\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
                return;
            }
            $cutoff = now()->subMonthsNoOverflow(3);
            do {
                $expiredIds = \Illuminate\Support\Facades\DB::table('audit_logs')
                    ->where('occurred_at', '<', $cutoff)
                    ->orderBy('id')
                    ->limit(1000)
                    ->pluck('id');

                if ($expiredIds->isNotEmpty()) {
                    \Illuminate\Support\Facades\DB::table('audit_logs')
                        ->whereIn('id', $expiredIds)
                        ->delete();
                }
            } while ($expiredIds->count() === 1000);
        })->dailyAt('02:30')->name('audit-log-retention')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
