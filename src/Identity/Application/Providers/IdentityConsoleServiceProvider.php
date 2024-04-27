<?php

namespace Identity\Application\Providers;

use App\Common\Application\Traits\ConsoleCommandLoad;
use Illuminate\Support\ServiceProvider;
use ReflectionException;
use Illuminate\Console\Scheduling\Schedule;

class IdentityConsoleServiceProvider extends ServiceProvider
{
    use ConsoleCommandLoad;

    protected string $base_dir = 'src'
        . DIRECTORY_SEPARATOR . 'Identity'
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
     * @return void
     */
    public function boot(Schedule $schedule): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }


    }
}
