<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPTimeout as TOTPTimeoutAttribute;

class TOTPTimeout implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new TOTPTimeoutAttribute((int) $value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof TOTPTimeoutAttribute) {
            throw new InvalidArgumentException('The given value is not instance of TOTPTimeout Attribute');
        }

        return [
            'totp_timeout' => $value->getValue(),
        ];
    }
}
