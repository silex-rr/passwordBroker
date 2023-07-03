<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FieldRestored extends FieldEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'restored';
    public function broadcastOn(): Channel
    {
        return new Channel('field-changes.' . $this->field->field_id);
    }

    public function broadcastAs(): string
    {
        return 'field.restored';
    }

    public function handle(): void
    {

    }
}
