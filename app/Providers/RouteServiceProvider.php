<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';


    protected string $password_namespace = 'PasswordBroker\Application\Http\Controllers';
    protected string $password_dir = 'src'
        . DIRECTORY_SEPARATOR . 'PasswordBroker'
        . DIRECTORY_SEPARATOR . 'Application'
        . DIRECTORY_SEPARATOR . 'Routes'
        . DIRECTORY_SEPARATOR;

    protected string $identity_namespace = 'Identity\Application\Http\Controllers';
    protected string $identity_dir = 'src'
        . DIRECTORY_SEPARATOR . 'Identity'
        . DIRECTORY_SEPARATOR . 'Application'
        . DIRECTORY_SEPARATOR . 'Routes'
        . DIRECTORY_SEPARATOR;

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $routeServiceProvider = $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes' . DIRECTORY_SEPARATOR . 'api.php'));

            Route::middleware('web')
                ->group(base_path('routes' . DIRECTORY_SEPARATOR . 'web.php'));

            Route::middleware('web')
                ->namespace($this->password_namespace)
                ->prefix('passwordBroker')
                ->group(base_path($this->password_dir . 'web.php'));
            Route::middleware('api')
                ->prefix('passwordBroker/api')
                ->namespace($this->password_namespace . '\Api')
                ->group(base_path($this->password_dir . 'api.php'));

            Route::middleware('api')
                ->prefix('identity/api')
                ->namespace($this->identity_namespace)
                ->group(base_path($this->identity_dir . 'api.php'));
        });

        if (env('LOG_LEVEL') === 'debug') {
            $routeServiceProvider->middleware('logRequest');
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
