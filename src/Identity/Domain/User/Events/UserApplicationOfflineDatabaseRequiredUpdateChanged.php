<?php

namespace Identity\Domain\User\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserApplicationOfflineDatabaseRequiredUpdateChanged extends DomainEvent
{
    use SerializesModels, Dispatchable;

    public function __construct(
        public readonly UserApplication $userApplication
    )
    {
        $this->entity = $this->userApplication;
    }

    /**
     * @return UserApplication
     */
    public function getEventBody(): string
    {
        return (string)$this->userApplication;
    }
}
