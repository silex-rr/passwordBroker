<?php

namespace Identity\Application\Providers;

use App\Common\Application\Traits\ProviderMergeConfigRecursion;
use Identity\Application\Http\Sessions\DatabaseSessionHandler;
use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    use ProviderMergeConfigRecursion;
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
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'filesystems.disks.php',
            'filesystems.disks'
        );
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'auth.providers.php',
            'auth.providers'
        );
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'database.connections.php',
            'database.connections'
        );
        $this->bindRoutes();

    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
//        Session::resolved(static function ($session) {
//            $session->extend('screen-session', function ($app) {
//                $table = $app['config']['session.table'];
//                $lifetime = $app['config']['session.lifetime'];
//                $connection = $app['db']->connection($app['config']['session.connection']);
//                return new DatabaseSessionHandler($connection, $table, $lifetime, $app);
//            });
//        });



        $this->loadMigrationsFrom($this->base_path . $this->migrations_dir);
    }

    public function bindRoutes(): void
    {
        Route::bind('user', fn (string $user_id) => User::where('user_id', $user_id)->firstOrFail());
    }
}
