<?php

namespace PasswordBroker\Domain\Entry\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_Title", type: "string",)]
class Title extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }

}
