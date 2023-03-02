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
use PasswordBroker\Domain\Entry\Models\Groups\Admin;

class AdminWasRemovedFromEntryGroup extends DomainEvent
    implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, Saveable;
    public function __construct(public Admin $admin)
    {
        $this->entity = $admin;
        /**
         * @var User $user
         */
        $user = Auth::user();
        $this->user = $user;
    }


    /**
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(self::CHANNEL_DOMAIN_EVENTS);
    }
}
