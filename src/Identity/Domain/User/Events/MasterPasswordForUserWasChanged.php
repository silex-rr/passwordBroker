<?php

namespace Identity\Domain\User\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MasterPasswordForUserWasChanged extends DomainEvent
{
    use SerializesModels, Dispatchable;

    public function __construct(
        public User $user
    )
    {
        $this->entity = $user;
    }

    /**
     * @return User
     */
    public function getEventBody(): string
    {
        return (string)$this->user;
    }
}
