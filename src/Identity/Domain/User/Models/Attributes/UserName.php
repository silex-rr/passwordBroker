<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;

#[Schema(schema: "Identity_UserName", type: "string",)]
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
