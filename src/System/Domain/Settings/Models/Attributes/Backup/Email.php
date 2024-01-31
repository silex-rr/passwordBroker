<?php

namespace System\Domain\Settings\Models\Attributes\Backup;

use App\Models\Abstracts\AbstractValue;

class Email extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
