<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;

#[Schema(schema: "Identity_RecoveryLinkKey", type: "string",)]
class RecoveryLinkKey extends AbstractValue
{
    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Recovery link key cannot be empty');
        }
        $this->value = $value;
    }
}
