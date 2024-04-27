<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\RecoveryLinkKey as RecoveryLinkKeyAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class RecoveryLinkKey implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): RecoveryLinkKeyAttribute
    {
        return new RecoveryLinkKeyAttribute($value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof RecoveryLinkKeyAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of RecoveryLinkKey Attribute.");
        }
        return [
            'key' => $value->getValue()
        ];
    }

}
