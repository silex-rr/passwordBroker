<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class IsAdmin extends AbstractValue
{
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
}
