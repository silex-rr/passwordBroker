<?php

namespace PasswordBroker\Domain\Entry\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryId as EntryIdAttribute;

class EntryId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): EntryIdAttribute
    {
        return new EntryIdAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (!$value instanceof EntryIdAttribute) {
            throw new InvalidArgumentException('The given value is not instance of EntryId Attribute');
        }
        return [
            'entry_id' => $value->getValue()
        ];
    }
}
