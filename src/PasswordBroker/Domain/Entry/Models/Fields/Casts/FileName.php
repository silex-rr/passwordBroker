<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileName as FIleNameAttribute;

class FileName implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new FIleNameAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof FIleNameAttribute) {
            throw new InvalidArgumentException('The given value is not instance of File Name Attribute');
        }

        return [
            'file_name' => $value->getValue()
        ];
    }
}
