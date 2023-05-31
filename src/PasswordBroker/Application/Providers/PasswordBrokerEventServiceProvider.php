<?php

namespace PasswordBroker\Application\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Application\Listeners\LogFieldChanges;

class PasswordBrokerEventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        FieldUpdated::class => [
            LogFieldChanges::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    public function discoverEventsWithin()
    {
        return [
            $this->app->basePath(base_path('src/PasswordBroker/Application/Listeners')),
        ];
    }
}
