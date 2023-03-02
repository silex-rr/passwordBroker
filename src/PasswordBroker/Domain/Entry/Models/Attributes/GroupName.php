<?php

namespace PasswordBroker\Domain\Entry\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class GroupName extends AbstractValue
{
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
}
