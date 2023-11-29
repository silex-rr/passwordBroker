<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class Size extends AbstractValue
{
    public function __construct(?int $value)
    {
        $this->value = $value;
    }
}
