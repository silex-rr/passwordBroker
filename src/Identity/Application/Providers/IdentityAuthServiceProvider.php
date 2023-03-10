<?php

namespace Identity\Application\Providers;

use Identity\Application\Policies\UserPolicy;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class IdentityAuthServiceProvider extends AuthServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
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
