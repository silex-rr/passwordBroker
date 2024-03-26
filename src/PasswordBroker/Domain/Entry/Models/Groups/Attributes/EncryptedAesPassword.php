<?php

namespace PasswordBroker\Domain\Entry\Models\Groups\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_EncryptedAesPassword", type: "string")]
class EncryptedAesPassword extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
