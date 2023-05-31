<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class FieldUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;
    public function __construct(public Field $field)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('field-changes.' . $this->field->field_id);
    }

    public function broadcastAs(): string
    {
        return 'user.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'field_id' => $this->field->field_id->getValue(),
            'changes' => $this->field->getChanges(),
        ];
    }

    public function handle(): void
    {

    }
}
