<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_ErrorMessage", type: "string", nullable: true)]
class ErrorMessage extends AbstractValue
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
