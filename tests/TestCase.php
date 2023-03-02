<?php

namespace Tests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsGuest() {
        DB::connection()->setTablePrefix(app(User::class)->getConnection()->getTablePrefix());
        Auth::logout();
    }
}
