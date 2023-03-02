<?php

namespace App\Console;

use Identity\Application\Console\Commands\AddUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
//    protected $commands = [
//        AddUser::class
//    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
//        dd($this->app->getNamespace());
        $this->load(__DIR__.'/Commands');
//        $this->load(__DIR__ . '/../src/identity/Application/Console/Commands');

        require base_path('routes/console.php');
    }
}
