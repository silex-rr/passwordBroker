<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAdminDeleted extends RoleEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'adminDeleted';

    public function broadcastOn(): Channel
    {
        return new Channel('roleAdmin-deleted.' . $this->role->entry_group_id->getValue());
    }

    public function broadcastAs(): string
    {
        return 'roleAdmin.created';
    }

    public function handle(): void
    {

    }
}
