<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use System\Domain\Backup\Models\Attributes\Size as SizeAttribute;

class Size implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new SizeAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof SizeAttribute) {
            throw new \InvalidArgumentException("The given value is not instance of Size Attribute");
        }
        return [
            'size' => $value->getValue(),
        ];
    }
}
