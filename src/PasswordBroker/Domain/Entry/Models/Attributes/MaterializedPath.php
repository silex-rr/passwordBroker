<?php

namespace PasswordBroker\Domain\Entry\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_MaterializedPath", type: "string")]
class MaterializedPath extends AbstractValue
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}
