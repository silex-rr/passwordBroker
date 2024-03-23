<?php

namespace System\Domain\Settings\Models\Attributes\Backup;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_Email", type: "string", format: "email",)]
class Email extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
