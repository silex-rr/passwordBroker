<?php

namespace PasswordBroker\Domain\Entry\Repository;

use DateTimeImmutable;
use Illuminate\Database\Query\Builder;
use PasswordBroker\Domain\Entry\Models\Entry;
use Ramsey\Uuid\Uuid;

class EloquentEntryRepository
    implements EntryRepository
{

    public function nextIdentity(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function add(Entry $entry): void
    {
        // TODO: Implement add() method.
    }

    public function remove(Entry $entry): void
    {
        // TODO: Implement remove() method.
    }

    public function createdBetween(DateTimeImmutable $from, DateTimeImmutable $to): Builder
    {
        return Entry::whereBetween('created_at', [
            $from->format('c'),
            $to->format('c')
        ]);
    }
}
