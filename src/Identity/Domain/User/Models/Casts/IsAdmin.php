<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\IsAdmin as IsAdminAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class IsAdmin implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): IsAdminAttribute
    {
        return new IsAdminAttribute(is_bool($value) ? $value : (bool)$value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof IsAdminAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of IsAdmin Attribute.");
        }
        return [
            'is_admin' => $value->getValue()
        ];
    }

}
