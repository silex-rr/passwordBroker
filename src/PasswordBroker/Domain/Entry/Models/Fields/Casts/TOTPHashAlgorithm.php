<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm as TOTPHashAlgorithmAttribute;

class TOTPHashAlgorithm implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): TOTPHashAlgorithmAttribute
    {
        return TOTPHashAlgorithmAttribute::from($value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (!$value instanceof TOTPHashAlgorithmAttribute) {
            throw new InvalidArgumentException("The given value is not an instance of TOTPHashAlgorithm Attribute.");
        }

        return [
            'totp_hash_algorithm' => $value->value,
        ];
    }

}
