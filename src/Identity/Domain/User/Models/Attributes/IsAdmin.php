<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;


#[Schema(schema: "Identity_IsAdmin", type: "boolean",)]
class IsAdmin extends AbstractValue
{
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
}
