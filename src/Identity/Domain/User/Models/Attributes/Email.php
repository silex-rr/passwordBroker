<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;

#[Schema(schema: "Identity_Email", type: "string", format: "email")]
class Email extends AbstractValue
{
    public function __construct(string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Got an invalid EMAIL '" . $value . "' as an user attribute");
        }
        $this->value = $value;
    }
}
