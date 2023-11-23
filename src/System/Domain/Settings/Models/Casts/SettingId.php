<?php

namespace System\Domain\Settings\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Settings\Models\Attributes\SettingId as SettingIdAttribute;

class SettingId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new SettingIdAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof SettingIdAttribute) {
            throw new InvalidArgumentException('The given value is not instance of SettingId Attribute');
        }
        return [
            'setting_id' => $value->getValue()
        ];
    }
}
