<?php

namespace PasswordBroker\Domain\Entry\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class Title extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }

}
