<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;

class ValueEncrypted extends AbstractValue
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }

}
