<?php

namespace PasswordBroker\Domain\Entry\Events;

use App\Common\Domain\Events\DomainEvent;
use App\Common\Domain\Traits\Saveable;
use Identity\Domain\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Domain\Entry\Models\Entry;

class EntryWasCreated extends DomainEvent
    implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, Saveable;

    //SerializesModels

    public Entry $entry;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
        $this->entity = $entry;
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
