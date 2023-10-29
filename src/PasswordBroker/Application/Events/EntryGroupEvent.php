<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

abstract class EntryGroupEvent implements ShouldQueue
{
    public const EVENT_TYPE = '';
    public function __construct(public EntryGroup $entryGroup)
    {
    }
    public function broadcastWith(): array
    {
        return [
            'entry_group_id' => $this->entryGroup->entry_group_id->getValue(),
            'changes' => $this->entryGroup->getChanges(),
            'event_type' => static::EVENT_TYPE
        ];
    }

    public function getEventType():string
    {
        return static::EVENT_TYPE;
    }
}
