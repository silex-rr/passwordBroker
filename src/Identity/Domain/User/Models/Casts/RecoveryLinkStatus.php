<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\RecoveryLinkStatus as RecoveryLinkStatusAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class RecoveryLinkStatus implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): RecoveryLinkStatusAttribute
    {
        return RecoveryLinkStatusAttribute::from($value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof RecoveryLinkStatusAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of RecoveryLinkStatus Attribute.");
        }
        return [
            'status' => $value->value
        ];
    }

}
