<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_Size", type: "integer")]
class Size extends AbstractValue
{
    public function __construct(?int $value)
    {
        $this->value = $value;
    }
}
