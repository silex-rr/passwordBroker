<?php

namespace Identity\Domain\User\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserWasCreated extends DomainEvent
{
    use SerializesModels, Dispatchable;

    public User $userCreated;

    public function __construct(User $user)
    {
        $this->entity = $user;
        $this->userCreated = $user;
    }

    /**
     * @return User
     */
    public function getEventBody(): string
    {
        return (string)$this->userCreated;
    }
}
