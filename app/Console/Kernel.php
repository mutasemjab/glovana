<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Appointment reminders - every minute
        $schedule->command('appointments:send-reminders')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/appointment-reminders.log'));

        // Check hourly appointments status - every minute
        $schedule->command('appointments:check-hourly-status')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/hourly-appointments-check.log'));

        // Check expired subscriptions - daily
        $schedule->command('subscriptions:check-expired')
            ->daily()
            ->appendOutputTo(storage_path('logs/subscriptions-check.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
