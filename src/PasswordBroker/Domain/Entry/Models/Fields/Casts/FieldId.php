<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldId as FieldIdAttribute;

class FieldId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): FieldIdAttribute
    {
        return new FieldIdAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof FieldIdAttribute) {
            throw new InvalidArgumentException('The given value is not instance of FiledId Attribute');
        }

        return [
            'field_id' => $value->getValue()
        ];
    }
}
