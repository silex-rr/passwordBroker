<?php

namespace PasswordBroker\Application\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

abstract class FieldEvent implements ShouldQueue
{
    public const EVENT_TYPE = '';
    public function __construct(public Field $field)
    {
    }
    public function broadcastWith(): array
    {
        return [
            'field_id' => $this->field->field_id->getValue(),
            'changes' => $this->field->getChanges(),
            'event_type' => static::EVENT_TYPE
        ];
    }

    public function getEventType():string
    {
        return static::EVENT_TYPE;
    }
}
