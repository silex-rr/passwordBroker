<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileMime as FileMimeAttribute;

class FileMime implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new FileMimeAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof FileMimeAttribute) {
            throw new InvalidArgumentException('The given value is not instance of File Mime Attribute');
        }

        return [
            'file_mime' => $value->getValue()
        ];
    }
}
