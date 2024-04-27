<?php

namespace Identity\Domain\User\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRecoveryLinkWasCreated extends DomainEvent
{
    use SerializesModels, Dispatchable;

    public function __construct(
        public readonly RecoveryLink $recoveryLink
    )
    {
        $this->entity = $this->recoveryLink;
    }

    /**
     * @return UserApplication
     */
    public function getEventBody(): string
    {
        return (string)$this->recoveryLink;
    }
}
