<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_FileSize", type: "integer",)]
class FileSize extends AbstractValue
{
    public function __construct(?int $value)
    {
        $this->value = $value;
    }
}
