<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryUpdated extends EntryEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'updated';
    public function broadcastOn(): Channel
    {
        return new Channel('entry-changes.' . $this->entry->entry_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'entry.updated';
    }

    public function handle(): void
    {

    }
}
