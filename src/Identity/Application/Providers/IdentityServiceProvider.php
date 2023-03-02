<?php

namespace Identity\Application\Providers;

use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{

    private string $migrations_dir = 'Infrastructure'
        . DIRECTORY_SEPARATOR . 'Database'
        . DIRECTORY_SEPARATOR . 'migrations';
    private string $configs_dir = 'Application'
        . DIRECTORY_SEPARATOR . 'config';

    private string $base_path;

    public function __construct($app)
    {
        $this->base_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        parent::__construct($app);
    }


    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'filesystems.disks.php', 'filesystems.disks');
        $this->mergeConfigFrom($this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'auth.providers.php', 'auth.providers');
        $this->bindRoutes();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom($this->base_path . $this->migrations_dir);
    }

    public function bindRoutes(): void
    {
        Route::bind('user', fn (string $user_id) => User::where('user_id', $user_id)->firstOrFail());
    }
}
