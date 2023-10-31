<?php

namespace Identity\Domain\UserApplication\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserApplicationOfflineDatabaseModeHasChanged extends DomainEvent
{
    use SerializesModels;
    use Dispatchable;

    public function __construct(
        public readonly UserApplication $userApplication,
        public readonly IsOfflineDatabaseMode $isOfflineDatabaseMode
    ) {
        $this->entity = $this->userApplication;
    }

    public function getEventBody(): string
    {
        return (string)$this->isOfflineDatabaseMode;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
