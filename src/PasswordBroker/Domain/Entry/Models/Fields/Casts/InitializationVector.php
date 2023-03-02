<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\InitializationVector as InitializationVectorAttribute;

class InitializationVector implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): InitializationVectorAttribute
    {
        return new InitializationVectorAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof InitializationVectorAttribute) {
            throw new InvalidArgumentException('The given value is not an instance of InitializationVector Attribute ');
        }
        return [
            'initialization_vector' => $value->getValue()
        ];
    }
}
