<?php

namespace System\Application\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class SystemAuthServiceProvider extends AuthServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [
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
