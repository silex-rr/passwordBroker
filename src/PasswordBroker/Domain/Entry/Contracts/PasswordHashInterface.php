<?php

namespace PasswordBroker\Domain\Entry\Contracts;

interface PasswordHashInterface
{
    public function hash(): string;
    public function setPlainPassword(string $plain): void;
}
