<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\RecoveryLinkType as RecoveryLinkTypeAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class RecoveryLinkType implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): RecoveryLinkTypeAttribute
    {
        return RecoveryLinkTypeAttribute::from($value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof RecoveryLinkTypeAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of RecoveryLinkType Attribute.");
        }
        return [
            'type' => $value->value
        ];
    }

}
