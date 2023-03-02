<?php

namespace PasswordBroker\Domain\Entry\Events;

use App\Common\Domain\Events\DomainEvent;
use Identity\Domain\User\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

class EntryWasMoved extends DomainEvent
    implements ShouldQueue
{
    public function __construct(
        public Entry $entry,
        public EntryGroup $entryGroupSource,
        public EntryGroup $entryGroupTarget
    )
    {
        $this->entity = $this->entry;
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
