<?php

namespace System\Domain\Settings\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;
use RuntimeException;
use System\Domain\Settings\Models\Attributes\Data as DataAttribute;

class Data implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new DataAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof DataAttribute) {
            throw new \InvalidArgumentException('The given value is not instance of Setting.Data Attribute');
        }
        return [
            'data' => $value->getValue(),
        ];
    }
}
