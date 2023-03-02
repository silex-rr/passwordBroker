<?php

namespace PasswordBroker\Domain\Entry\Contracts;

interface EntryRepositoryInterface
{
    public function query(EntrySpecificationInterface $specification);
}
