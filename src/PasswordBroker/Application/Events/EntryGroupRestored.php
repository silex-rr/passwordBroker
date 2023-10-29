<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryGroupRestored extends EntryGroupEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'restored';
    public function broadcastOn(): Channel
    {
        return new Channel('entryGroup-changes.' . $this->entryGroup->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'entryGroup.restored';
    }

    public function handle(): void
    {

    }
}
