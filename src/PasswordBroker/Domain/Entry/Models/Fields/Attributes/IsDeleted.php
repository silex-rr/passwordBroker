<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;

class IsDeleted extends AbstractValue
{

    public function __construct(bool $value)
    {
        $this->value = $value;
    }
}
