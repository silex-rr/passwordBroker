<?php

namespace PasswordBroker\Infrastructure\Repositories;

use App\Common\Domain\Abstractions\BaseRepository;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;

class EntryFieldHistoryRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return EntryFieldHistory::class;
    }


}
