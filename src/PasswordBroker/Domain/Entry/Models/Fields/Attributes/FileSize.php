<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;

class FileSize extends AbstractValue
{

    public function __construct(?int $value)
    {
        $this->value = $value;
    }
}
