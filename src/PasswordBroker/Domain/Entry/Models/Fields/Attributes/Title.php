<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_FieldTitle", type: "string")]
class Title extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }

}
