<?php

namespace System\Application\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use System\Application\Observers\SettingObserver;
use System\Domain\Settings\Models\Setting;

class SystemEventServiceProvider extends EventServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        foreach (Setting::getRelated() as $class) {
            $class::observe(SettingObserver::class);
        }
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
            $this->app->basePath(base_path('src/System/Application/Listeners')),
        ];
    }
}
