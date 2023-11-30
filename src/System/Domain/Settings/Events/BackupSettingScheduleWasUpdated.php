<?php

namespace System\Domain\Settings\Events;

use App\Common\Domain\Events\DomainEvent;
use App\Common\Domain\Traits\Saveable;
use Identity\Domain\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Auth;
use System\Domain\Settings\Models\BackupSetting;

class BackupSettingScheduleWasUpdated extends DomainEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, Saveable;

    public function __construct(public BackupSetting $backupSetting)
    {
        $this->entity = $this->backupSetting;
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
