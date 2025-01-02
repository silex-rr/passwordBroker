<?php

namespace Tests\Unit\PasswordBroker\Infrastructure;

use Illuminate\Foundation\Testing\WithFaker;
use PasswordBroker\Infrastructure\Services\TimeBasedOneTimePasswordGenerator;
use Tests\TestCase;

class TimeBasedOneTimePasswordGeneratorTest extends TestCase
{
    use WithFaker;
    public function test_password_generator_can_create_one_time_password(): void
    {
        $secret = $this->faker->word;
        $timeBasedOneTimePasswordGenerator = new TimeBasedOneTimePasswordGenerator();
        $TOTP = $timeBasedOneTimePasswordGenerator->generate($secret);
        $this->assertNotEmpty($TOTP->now());
    }
}
