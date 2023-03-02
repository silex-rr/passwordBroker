<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class PublicKey extends AbstractValue
{
    public function __construct(string $value) {
        $this->value = $value;
    }
}
