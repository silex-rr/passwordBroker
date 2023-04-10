<?php

namespace PasswordBroker\Domain\Entry\Models\Attributes;

class MaterializedPath extends \App\Models\Abstracts\AbstractValue
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}
