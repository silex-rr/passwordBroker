<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleMemberDeleted extends RoleEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'memberDeleted';

    public function broadcastOn(): Channel
    {
        return new Channel('roleMember-deleted.' . $this->role->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'roleMember.created';
    }

    public function handle(): void
    {

    }
}
