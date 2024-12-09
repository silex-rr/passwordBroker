<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_TOPTTimeout", type: "integer",)]
class TOTPTimeout extends AbstractValue
{
    public function __construct(?int $value)
    {
        $this->value = $value;
    }
}
