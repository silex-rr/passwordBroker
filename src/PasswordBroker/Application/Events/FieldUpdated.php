<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Application\Listeners\LogFieldChanges;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
#[LogFieldChanges]
class FieldUpdated extends FieldEvent
{
    use Dispatchable, SerializesModels;

    public const EVENT_TYPE = 'updated';
    public function broadcastOn(): Channel
    {
        return new Channel('field-changes.' . $this->field->field_id);
    }

    public function broadcastAs(): string
    {
        return 'field.updated';
    }

    public function handle(): void
    {

    }
}
