<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class FileName extends AbstractValue
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
