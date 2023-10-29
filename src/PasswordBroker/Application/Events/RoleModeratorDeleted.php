<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleModeratorDeleted extends RoleEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'moderatorDeleted';

    public function broadcastOn(): Channel
    {
        return new Channel('roleModerator-deleted.' . $this->role->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'roleModerator.created';
    }

    public function handle(): void
    {

    }
}
