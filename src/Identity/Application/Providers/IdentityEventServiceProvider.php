<?php

namespace Identity\Application\Providers;

use Identity\Application\Listeners\UserApplicationSetOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Events\UserApplicationOfflineDatabaseModeHasChanged;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use PasswordBroker\Application\Events\EntryCreated;
use PasswordBroker\Application\Events\EntryGroupCreated;
use PasswordBroker\Application\Events\EntryGroupRestored;
use PasswordBroker\Application\Events\EntryGroupTrashed;
use PasswordBroker\Application\Events\EntryGroupUpdated;
use PasswordBroker\Application\Events\EntryRestored;
use PasswordBroker\Application\Events\EntryTrashed;
use PasswordBroker\Application\Events\EntryUpdated;
use PasswordBroker\Application\Events\FieldCreated;
use PasswordBroker\Application\Events\FieldRestored;
use PasswordBroker\Application\Events\FieldTrashed;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Application\Events\RoleAdminCreated;
use PasswordBroker\Application\Events\RoleAdminDeleted;
use PasswordBroker\Application\Events\RoleMemberCreated;
use PasswordBroker\Application\Events\RoleMemberDeleted;
use PasswordBroker\Application\Events\RoleModeratorCreated;
use PasswordBroker\Application\Events\RoleModeratorDeleted;

class IdentityEventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        UserApplicationOfflineDatabaseModeHasChanged::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        //Fields Events
        FieldUpdated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        FieldCreated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        FieldTrashed::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        FieldRestored::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        //Role Events
        RoleAdminCreated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        RoleAdminDeleted::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        RoleMemberCreated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        RoleMemberDeleted::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        RoleModeratorCreated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        RoleModeratorDeleted::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        //Entry Group Events
//        EntryGroupCreated::class => [
//            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
//        ],
        EntryGroupTrashed::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        EntryGroupRestored::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        EntryGroupUpdated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        //Entry Events
        EntryCreated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        EntryTrashed::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        EntryRestored::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
        ],
        EntryUpdated::class => [
            UserApplicationSetOfflineDatabaseRequiredUpdate::class,
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
            $this->app->basePath(base_path('src/Identity/Application/Listeners')),
        ];
    }
}
