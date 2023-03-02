<?php

namespace PasswordBroker\Domain\Entry\Models\Groups\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Groups\Attributes\EncryptedAesPassword as EncryptedAesPasswordAttribute;

class EncryptedAesPassword implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new EncryptedAesPasswordAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof EncryptedAesPasswordAttribute) {
            throw new InvalidArgumentException('The given values is not instance of EncryptedAswPassword Attribute');
        }

        return [
            'encrypted_aes_password' => $value->getValue()
        ];
    }
}
