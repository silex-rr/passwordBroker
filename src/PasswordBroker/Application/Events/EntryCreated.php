<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryCreated extends EntryEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'created';

    public function broadcastOn(): Channel
    {
        return new Channel('entry-created.' . $this->entry->entry_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'entry.created';
    }

    public function handle(): void
    {

    }
}
