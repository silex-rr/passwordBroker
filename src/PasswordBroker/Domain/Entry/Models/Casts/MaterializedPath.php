<?php

namespace PasswordBroker\Domain\Entry\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Attributes\MaterializedPath as MaterializedPathAttribute;

class MaterializedPath implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new MaterializedPathAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof MaterializedPathAttribute) {
            throw new InvalidArgumentException('THe given value is not instance of MaterializedPath Attribute');
        }

        return [
            'materialized_path' => $value->getValue()
        ];
    }
}
