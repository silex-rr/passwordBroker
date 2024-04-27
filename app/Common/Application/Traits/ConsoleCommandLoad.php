<?php

namespace App\Common\Application\Traits;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

trait ConsoleCommandLoad
{
    /**
     * @throws ReflectionException
     */
    protected function commandLoad($paths): void
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
                !(new ReflectionClass($command))->isAbstract()
            ) {
                Artisan::starting(static function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }
}
