<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\Collect::class,
        \App\Console\Commands\CollectAll::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $frequency = env('COLLECT_FREQUENCY', 'minute');

        switch ($frequency) {
            case 'minute':
                $schedule->command('collect:resource')->everyMinute();
                break;

            case 'hour':
                $schedule->command('collect:resource')->hourly();
                break;

            case 'day':
                $schedule->command('collect:resource')->daily();
                break;

            default :
                $schedule->command('collect:resource')->everyMinute();
        }


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
