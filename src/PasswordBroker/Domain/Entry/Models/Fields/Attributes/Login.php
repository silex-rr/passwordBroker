<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_Login", type: "string")]
class Login extends AbstractValue
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}
