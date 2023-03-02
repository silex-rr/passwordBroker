<?php

namespace PasswordBroker\Domain\Entry\Contracts;

use PasswordBroker\Domain\Entry\Models\Fields\Password;

interface EntrySpecificationInterface
{
    public function specifies(Password $password): bool;
}
