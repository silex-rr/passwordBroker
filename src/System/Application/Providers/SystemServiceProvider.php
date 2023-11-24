<?php

namespace System\Application\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use System\Domain\Settings\Models\BackupScheduleSetting;

class SystemServiceProvider extends ServiceProvider
{
    private string $migrations_dir = 'Infrastructure'
        . DIRECTORY_SEPARATOR . 'Database'
        . DIRECTORY_SEPARATOR . 'migrations';
    private string $base_path;

    public function __construct($app)
    {
        $this->base_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        parent::__construct($app);
    }


    public function boot(): void
    {
        $this->loadMigrationsFrom($this->base_path . $this->migrations_dir);
        $this->bindRoutes();
        $this->defineGates();
    }

    public function register(): void
    {
//        $this->mergeConfigRecursion(
//            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'password.php',
//            'passwordBroker'
//        );
    }

    public function bindRoutes(): void
    {
        Route::bind('backupScheduleSetting', fn() => BackupScheduleSetting::firstOrCreate([
            'key' => BackupScheduleSetting::TYPE,
            'type' => BackupScheduleSetting::TYPE,
        ]));
    }

    public function defineGates(): void
    {
//        Gate::define('field-history-search-any', static fn(User $user) =>
//        $user->is_admin->getValue()
//            ? Response::allow()
//            : Response::deny('You must be a system administrator')
//        );
    }
}
