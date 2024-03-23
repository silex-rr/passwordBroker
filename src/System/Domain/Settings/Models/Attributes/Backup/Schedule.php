<?php

namespace System\Domain\Settings\Models\Attributes\Backup;

use App\Models\Abstracts\AbstractValue;
use App\Models\Interfaces\ValueObject;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Schema;
use phpDocumentor\Reflection\Type;

#[Schema(schema: "System_Schedule", type: "array", items: new Items(type: "integer", format: "hours"))]
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

    /**
     * @param Schedule $object
     * @return bool
     */
    public function equals(ValueObject $object): bool
    {
        return count(array_diff($this->value, $object->value)) === 0;
    }
}
