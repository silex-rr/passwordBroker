<?php

namespace Identity\Domain\UserApplication\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserApplicationWasCreated extends DomainEvent
{
    use SerializesModels;
    use Dispatchable;

    public function __construct(
        private readonly UserApplication $userApplication
    ) {
        $this->entity = $this->userApplication;
    }

    public function getEventBody(): string
    {
        return (string)$this->userApplication;
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
