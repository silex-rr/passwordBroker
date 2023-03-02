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
use PasswordBroker\Domain\Entry\Models\Groups\Admin;

class AdminWasAddedToEntryGroup extends DomainEvent
    implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Saveable;

    public Admin $admin;

    public function __construct(Admin $admin)
    {
        $this->entity = $admin;
        $this->admin = $admin;
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
