<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldEditLog;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_EventType", type: "string")]
class EventType extends AbstractValue
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
