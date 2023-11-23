<?php

namespace System\Domain\Settings\Models\Attributes\Backup;

use App\Models\Abstracts\AbstractValue;

class Schedule extends AbstractValue
{
    public function __construct($value = null)
    {
        $this->value = [];
        if (is_null($value)) {
            return;
        }
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $item) {
            if ((is_int($item) || ctype_digit($item))
                && $item >= 0 && $item < 23
            ) {
                $this->value[] = (int)$item;
            }
        }

    }
}
