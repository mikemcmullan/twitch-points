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
        \App\Console\Commands\UpdatePoints::class,
        \App\Console\Commands\RankChatters::class,
        \App\Console\Commands\SyncSystemStatus::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('points:sync-status jonzzzzz')->everyFiveMinutes();
        // $schedule->command('points:update')->everyFiveMinutes();
        // $schedule->command('points:rank')->everyTenMinutes();
    }
}
