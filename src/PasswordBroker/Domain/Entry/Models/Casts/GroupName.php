<?php

namespace PasswordBroker\Domain\Entry\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName as GroupNameAttribute;

class GroupName implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): GroupNameAttribute
    {
        return new GroupNameAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof GroupNameAttribute) {
            throw new InvalidArgumentException('THe given value is not instance of GroupName Attribute');
        }

        return [
            'name' => $value->getValue()
        ];
    }
}
