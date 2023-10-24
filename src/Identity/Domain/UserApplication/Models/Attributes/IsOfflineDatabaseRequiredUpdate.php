<?php

namespace Identity\Domain\UserApplication\Models\Attributes;

use App\Models\Abstracts\AbstractValue;

class IsOfflineDatabaseRequiredUpdate extends AbstractValue
{
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
}