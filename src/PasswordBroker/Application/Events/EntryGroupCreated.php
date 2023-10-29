<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryGroupCreated extends EntryGroupEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'created';

    public function broadcastOn(): Channel
    {
        return new Channel('entryGroup-created.' . $this->entryGroup->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'entryGroup.created';
    }

    public function handle(): void
    {

    }
}
