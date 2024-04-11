<?php

namespace System\Application\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class Kernel extends ConsoleKernel
{

    protected string $base_dir = 'src'
        . DIRECTORY_SEPARATOR . 'System'
        . DIRECTORY_SEPARATOR . 'Application';

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('system:backupCron')->everyFiveMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     * @throws ReflectionException
     */
    protected function commands(): void
    {
//        dd($this->app->getNamespace());
        $this->domainLoad($this->base_dir
            . DIRECTORY_SEPARATOR . 'Console'
            . DIRECTORY_SEPARATOR . 'Commands');
        require base_path($this->base_dir . DIRECTORY_SEPARATOR . 'Routes/console.php');
    }

    /**
     * @throws ReflectionException
     */
    protected function domainLoad($paths): void
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, static function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        foreach ((new Finder)->in($paths)->files() as $command) {

            $command = str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($command->getRealPath(), base_path('src') . DIRECTORY_SEPARATOR)
                );

            if (is_subclass_of($command, Command::class) &&
                !(new ReflectionClass($command))->isAbstract()) {
                Artisan::starting(static function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }
}
