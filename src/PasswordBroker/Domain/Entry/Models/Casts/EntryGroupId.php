<?php

namespace PasswordBroker\Domain\Entry\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId as GroupIdAttribute;

class EntryGroupId implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): GroupIdAttribute
    {
        return new GroupIdAttribute($value?:null);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if (!$value instanceof GroupIdAttribute) {
            throw new InvalidArgumentException("The given value is not instance of GroupId Attribute");
        }

        return [
            'entry_group_id' => $value->getValue()
        ];
    }

}
