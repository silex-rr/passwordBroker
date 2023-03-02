<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{


//    private string $password_broker_migrations_dir = 'src'
//        . DIRECTORY_SEPARATOR . 'PasswordBroker'
//        . DIRECTORY_SEPARATOR . 'Infrastructure'
//        . DIRECTORY_SEPARATOR . 'Database'
//        . DIRECTORY_SEPARATOR . 'migrations';
//    private string $password_broker_factories_dir = 'src'
//        . DIRECTORY_SEPARATOR . 'PasswordBroker'
//        . DIRECTORY_SEPARATOR . 'Infrastructure'
//        . DIRECTORY_SEPARATOR . 'Factories';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->loadMigrationsFrom([
//            database_path('migrations'),
//            base_path($this->identity_migrations_dir),
//            base_path($this->password_broker_migrations_dir),
//        ]);
//        $this->registerEloquentFactories($this->identity_factories_dir);
//        $this->registerEloquentFactories($this->password_broker_factories_dir);
    }
//
//    protected function registerEloquentFactories(string $path): void
//    {
//        $this->app->make(Factory::class)
//            ->load(base_path($path));
//    }

}
