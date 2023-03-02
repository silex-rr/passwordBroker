<?php

namespace PasswordBroker\Domain\Entry\Models\Groups\Attributes;

use App\Models\Abstracts\AbstractValue;

class EncryptedAesPassword extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
