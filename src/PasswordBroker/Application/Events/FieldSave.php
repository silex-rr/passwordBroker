<?php
namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class FieldSave
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Field $password
     */
    private Field $field;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Field $field)
    {
        $this->field = $field;
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
