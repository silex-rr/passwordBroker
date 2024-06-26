<?php

namespace Identity\Application\Providers;

use App\Common\Application\Traits\ProviderMergeConfigRecursion;
use Identity\Domain\User\Models\Casts\RecoveryLinkKey;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'view.paths.php',
            'view.paths'
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


        $this->defineGates();
        $this->loadMigrationsFrom($this->base_path . $this->migrations_dir);
        Sanctum::usePersonalAccessTokenModel(UserAccessToken::class);
    }

    public function bindRoutes(): void
    {
        Route::bind('user', fn(string $user_id) => User::where('user_id', $user_id)->firstOrFail());
        Route::bind('userApplication', fn(string $uuid) => UserApplication::where('user_application_id', $uuid)->orWhere('client_id', $uuid)->firstOrFail());
        Route::bind('recoveryLink', function ($value, \Illuminate\Routing\Route $route) {
            $bindingFields = $route->bindingFields();
            if (array_key_exists('recoveryLink', $bindingFields)) {
                return RecoveryLink::where($bindingFields['recoveryLink'], $value)->first() ?? new RecoveryLink();
            }
            return RecoveryLink::where('recovery_link_id', $value)->first();
        });
    }

    private function defineGates(): void
    {
        Gate::define('get-self-rsa-private-key', static fn(User $user) => $user->is_admin->getValue());
        Gate::define('get-cbc-salt', static fn(User $user) => $user->is_admin->getValue());
        Gate::define('invite-new-user', static fn(User $user) => $user->is_admin->getValue());
    }
}
