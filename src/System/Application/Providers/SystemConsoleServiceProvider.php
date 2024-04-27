<?php

namespace System\Application\Providers;

use App\Common\Application\Traits\ConsoleCommandLoad;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class SystemConsoleServiceProvider extends ServiceProvider
{
    use ConsoleCommandLoad;

    protected string $base_dir = 'src'
    . DIRECTORY_SEPARATOR . 'System'
    . DIRECTORY_SEPARATOR . 'Application';

    /**
     * Register services.
     *
     * @return void
     * @throws ReflectionException
     */
    public function register(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commandLoad($this->base_dir
            . DIRECTORY_SEPARATOR . 'Console'
            . DIRECTORY_SEPARATOR . 'Commands');

        require base_path($this->base_dir . DIRECTORY_SEPARATOR . 'Routes/console.php');
    }

    /**
     * Bootstrap services.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function boot(Schedule $schedule): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        $schedule->command('system:backupCron')->everyFiveMinutes()->withoutOverlapping();
    }
}
