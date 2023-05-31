<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldEditLogId as FieldEditLogIdAttribute;

class FieldEditLogId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): FieldEditLogIdAttribute
    {
        return new FieldEditLogIdAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof FieldEditLogIdAttribute) {
            throw new InvalidArgumentException('The given value is not instance of FiledEditLogId Attribute');
        }

        return [
            'field_edit_log_id' => $value->getValue()
        ];
    }
}
