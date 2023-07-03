<?php

namespace PasswordBroker\Application\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use PasswordBroker\Application\Events\FieldCreated;
use PasswordBroker\Application\Events\FieldDecrypted;
use PasswordBroker\Application\Events\FieldForceDeleted;
use PasswordBroker\Application\Events\FieldRestored;
use PasswordBroker\Application\Events\FieldTrashed;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Application\Listeners\LogFieldChanges;
use PasswordBroker\Application\Listeners\LogFieldDelete;

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
        FieldCreated::class => [
            LogFieldChanges::class
        ],
        FieldDecrypted::class => [
            LogFieldChanges::class
        ],
        FieldTrashed::class => [
            LogFieldChanges::class
        ],
        FieldRestored::class => [
            LogFieldChanges::class
        ],

        FieldForceDeleted::class => [
            LogFieldDelete::class
        ]
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
