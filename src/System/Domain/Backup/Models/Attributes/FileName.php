<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_FileName", type: "string")]
class FileName extends AbstractValue
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
