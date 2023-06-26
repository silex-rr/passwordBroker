<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FieldCreated extends FieldEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'created';

    public function broadcastOn(): Channel
    {
        return new Channel('field-created.' . $this->field->field_id);
    }

    public function broadcastAs(): string
    {
        return 'field.created';
    }

    public function handle(): void
    {

    }
}
