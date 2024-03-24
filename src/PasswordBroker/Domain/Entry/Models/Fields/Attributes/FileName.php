<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_FileName", type: "string",)]
class FileName extends AbstractValue
{

    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
