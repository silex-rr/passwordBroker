<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryTrashed extends EntryEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'trashed';
    public function broadcastOn(): Channel
    {
        return new Channel('entry-changes.' . $this->entry->entry_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'entry.trashed';
    }

    public function handle(): void
    {

    }
}
