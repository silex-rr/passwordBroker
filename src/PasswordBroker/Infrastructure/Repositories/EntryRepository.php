<?php

namespace PasswordBroker\Infrastructure\Repositories;

use App\Common\Domain\Abstractions\BaseRepository;
use PasswordBroker\Domain\Entry\Models\Entry;

class EntryRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Entry::class;
    }
}
