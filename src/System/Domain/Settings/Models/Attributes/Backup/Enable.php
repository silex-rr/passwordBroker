<?php

namespace System\Domain\Settings\Models\Attributes\Backup;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_Enable", type: "boolean")]
class Enable extends AbstractValue
{
    public function __construct(?bool $value)
    {
        $this->value = is_bool($value) ? $value : false;
    }
}
