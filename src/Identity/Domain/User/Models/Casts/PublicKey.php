<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\PublicKey as PublicKeyAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class PublicKey implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): PublicKeyAttribute
    {
        return new PublicKeyAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (!$value instanceof PublicKeyAttribute) {
            throw new InvalidArgumentException('The given value is not instance of PublicKey Attribute');
        }

        return [
            'public_key' => $value->getValue()
        ];
    }
}
