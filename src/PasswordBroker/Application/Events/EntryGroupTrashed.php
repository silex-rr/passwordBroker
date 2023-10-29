<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryGroupTrashed extends EntryGroupEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'trashed';
    public function broadcastOn(): Channel
    {
        return new Channel('entryGroup-changes.' . $this->entryGroup->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'entryGroup.trashed';
    }

    public function handle(): void
    {

    }
}
