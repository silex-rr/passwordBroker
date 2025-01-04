<?php

namespace PasswordBroker\Domain\Entry\Events;

use App\Common\Domain\Events\DomainEvent;
use App\Common\Domain\Traits\Saveable;
use Identity\Domain\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

class EntryGroupWasUpdated extends DomainEvent
    implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Saveable;

    public EntryGroup $entryGroup;

    public function __construct(EntryGroup $entryGroup)
    {
        $this->entity = $entryGroup;
        $this->entryGroup = $entryGroup;
        /**
         * @var User $user
         */
        $user = Auth::user();
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(self::CHANNEL_DOMAIN_EVENTS);
    }
}
