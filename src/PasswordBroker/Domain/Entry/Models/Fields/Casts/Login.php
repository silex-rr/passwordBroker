<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login as LoginAttribute;

class Login implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new LoginAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof LoginAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Login Attribute');
        }

        return [
            'login' => $value->getValue()
        ];
    }
}
