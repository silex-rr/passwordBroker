<?php

namespace PasswordBroker\Domain\Entry\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Attributes\Title as TitleAttribute;

class Title implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): TitleAttribute
    {
        return new TitleAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (!$value instanceof TitleAttribute) {
            throw new InvalidArgumentException("The given value is not instance of Title Attribute");
        }
        return [
            'title' => $value->getValue()
        ];
    }
}
