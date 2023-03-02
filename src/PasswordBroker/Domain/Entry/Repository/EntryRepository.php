<?php

namespace PasswordBroker\Domain\Entry\Repository;

use PasswordBroker\Domain\Entry\Models\Entry;

interface EntryRepository
{
    public function nextIdentity(): string;
    public function add(Entry $entry): void;
    public function remove(Entry $entry): void;
}
