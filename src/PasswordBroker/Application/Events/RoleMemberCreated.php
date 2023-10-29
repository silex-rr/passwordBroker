<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleMemberCreated extends RoleEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'memberCreated';

    public function broadcastOn(): Channel
    {
        return new Channel('roleMember-created.' . $this->role->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'roleMember.created';
    }

    public function handle(): void
    {

    }
}
