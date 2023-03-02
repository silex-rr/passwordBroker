<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;

class UserName extends AbstractValue
{
    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('User name must contain at least one character');
        }
        $this->value = $value;
    }
}
