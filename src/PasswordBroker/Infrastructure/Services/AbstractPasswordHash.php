<?php

namespace PasswordBroker\Infrastructure\Services;

use PasswordBroker\Domain\Entry\Contracts\PasswordHashInterface;

abstract class AbstractPasswordHash
    implements PasswordHashInterface
{
    protected string $plain;

    abstract public function hash(): string;
    public function setPlainPassword(string $plain): void
    {
        $this->plain = $plain;
    }

}
