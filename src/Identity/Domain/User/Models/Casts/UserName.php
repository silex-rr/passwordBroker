<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\UserName as UserNameAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class UserName implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): UserNameAttribute
    {
        return new UserNameAttribute($value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof UserNameAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of UserName Attribute.");
        }
        return [
            'name' => $value->getValue()
        ];
    }

}
