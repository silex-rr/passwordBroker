<?php

namespace PasswordBroker\Infrastructure\Services;

use OTPHP\TOTP;

class TimeBasedOneTimePasswordGenerator
{
    public function generate(string $secret): TOTP
    {
        return TOTP::create($secret);
    }
}
