<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted as ValueEncryptedAttribute;

class ValueEncrypted implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): ValueEncryptedAttribute
    {

        return new ValueEncryptedAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof ValueEncryptedAttribute) {
            throw new InvalidArgumentException('The given value is not an instance of ValueEncrypted Attribute');
        }

        return [
            'value_encrypted' => $value->getValue()
        ];
    }
}
