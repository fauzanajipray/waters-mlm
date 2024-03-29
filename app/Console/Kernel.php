<?php

namespace App\Console;

use Carbon\Carbon;
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
        // $schedule->command('inspire')
        //     ->everyMinute()
        //     ->sendOutputTo(storage_path('logs/inspire.log'), true);

        $schedule->command('snapshot-levels')
            ->monthlyOn(1, '00:00')
            ->appendOutputTo(storage_path('logs/snapshot-levels.log'));

        $dayEndInMonth = Carbon::now()->endOfMonth()->format('d');
        $schedule->command('level-up-member')
            ->monthlyOn($dayEndInMonth, '23:00')
            ->appendOutputTo(storage_path('logs/level-up-member.log'));

        $schedule->command('bonus-nsi')
            ->monthlyOn($dayEndInMonth, '23:00')
            ->appendOutputTo(storage_path('logs/bonus-nsi.log'));
        $schedule->command('bonus-lsi')
            ->monthlyOn($dayEndInMonth, '23:00')
            ->appendOutputTo(storage_path('logs/bonus-lsi.log'));
        $schedule->command('bonus-ssi')
            ->monthlyOn($dayEndInMonth, '23:00')
            ->appendOutputTo(storage_path('logs/bonus-pm.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
