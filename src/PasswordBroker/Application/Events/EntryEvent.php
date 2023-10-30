<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

abstract class EntryEvent implements ShouldQueue
{
    public const EVENT_TYPE = '';
    public function __construct(public Entry $entry)
    {
    }
    public function broadcastWith(): array
    {
        return [
            'entry_id' => $this->entry->entry_id->getValue(),
            'changes' => $this->entry->getChanges(),
            'event_type' => static::EVENT_TYPE
        ];
    }

    public function getEventType():string
    {
        return static::EVENT_TYPE;
    }
}
