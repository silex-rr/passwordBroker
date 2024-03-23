<?php

namespace System\Domain\Settings\Models\Attributes\Backup;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_Password", type: "string",)]
class Password extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
