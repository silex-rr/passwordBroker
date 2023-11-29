<?php

namespace System\Domain\Backup\Events;

use App\Common\Domain\Events\DomainEvent;
use App\Common\Domain\Traits\Saveable;
use Identity\Domain\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use System\Domain\Backup\Models\Backup;

class BackupWasCreated extends DomainEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, Saveable, SerializesModels;

    public function __construct(public Backup $backup)
    {
        $this->entity = $this->backup;
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

    /**
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'backup.wasCreated';
    }
}
