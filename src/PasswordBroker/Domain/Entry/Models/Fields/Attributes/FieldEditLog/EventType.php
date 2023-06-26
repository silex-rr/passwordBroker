<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldEditLog;

use App\Models\Abstracts\AbstractValue;

class EventType extends AbstractValue
{

    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
