<?php

namespace PasswordBroker\Domain\Entry\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_GroupName", type: "string")]
class GroupName extends AbstractValue
{
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
}
