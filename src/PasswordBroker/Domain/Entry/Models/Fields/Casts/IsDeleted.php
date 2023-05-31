<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;


use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\IsDeleted as IsDeletedAttribute;

class IsDeleted implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): IsDeletedAttribute
    {
        return new IsDeletedAttribute(is_bool($value) ? $value : (bool)$value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof IsDeletedAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of IsDeleted Attribute.");
        }
        return [
            'is_deleted' => $value->getValue()
        ];
    }

}
