<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Application\Listeners\LogFieldChanges;

#[LogFieldChanges]
class FieldDecrypted extends FieldEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'decrypted';
    public function broadcastOn(): Channel
    {
        return new Channel('field-changes.' . $this->field->field_id);
    }

    public function broadcastAs(): string
    {
        return 'field.decrypted';
    }

    public function handle(): void
    {

    }
}
