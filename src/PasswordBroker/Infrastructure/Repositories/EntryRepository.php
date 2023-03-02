<?php

namespace PasswordBroker\Infrastructure\Repositories;

use PasswordBroker\Domain\Entry\Contracts\EntryRepositoryInterface;
use PasswordBroker\Domain\Entry\Contracts\EntrySpecificationInterface;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

class EntryRepository implements EntryRepositoryInterface
{
    public function query(EntrySpecificationInterface $specification)
    {
        return Password::get()->filter(
            fn(Password $password) => $specification->specifies($password)
        );
    }
}
