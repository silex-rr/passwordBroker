<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts\FieldEditLog;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldEditLog\EventType as EventTypeAttribute;

class EventType implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new EventTypeAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof EventTypeAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Event Type Attribute');
        }

        return [
            'event_type' => $value->getValue()
        ];
    }
}
