<?php

namespace PasswordBroker\Application\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use PasswordBroker\Application\Policies\EntryFieldPolicy;
use PasswordBroker\Application\Policies\EntryGroupHistoryPolicy;
use PasswordBroker\Application\Policies\EntryGroupPolicy;
use PasswordBroker\Application\Policies\EntryPolicy;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class PasswordBrokerAuthServiceProvider extends AuthServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [
        EntryGroup::class => EntryGroupPolicy::class,
        Entry::class => EntryPolicy::class,
        Field::class => EntryFieldPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
