<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Role;

abstract class RoleEvent implements ShouldQueue
{
    public const EVENT_TYPE = '';
    public function __construct(public Role $role)
    {
    }
    public function broadcastWith(): array
    {
        return [
            'entry_group_id' => $this->role->entry_group_id->getValue(),
            'user_id' => $this->role->user_id->getValue(),
            'changes' => $this->role->getChanges(),
            'event_type' => static::EVENT_TYPE
        ];
    }

    public function getEventType():string
    {
        return static::EVENT_TYPE;
    }


}
