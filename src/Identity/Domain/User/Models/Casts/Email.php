<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\Email as EmailAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class Email implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): EmailAttribute
    {
        return new EmailAttribute( $attributes['email']);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof EmailAttribute) {
            throw new InvalidArgumentException('The given value is not an Email Attribute instance.');
        }

        return [
            'email' => $value->getValue(),
        ];
    }
}
