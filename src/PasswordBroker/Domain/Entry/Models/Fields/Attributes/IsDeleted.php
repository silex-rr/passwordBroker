<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_IsDeleted", type: "boolean",)]
class IsDeleted extends AbstractValue
{
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
}
