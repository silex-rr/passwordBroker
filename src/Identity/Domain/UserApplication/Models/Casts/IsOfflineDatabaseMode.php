<?php

namespace Identity\Domain\UserApplication\Models\Casts;

use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode as IsOfflineDatabaseModeAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class IsOfflineDatabaseMode implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new IsOfflineDatabaseModeAttribute(is_bool($value) ? $value : (bool)$value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof IsOfflineDatabaseModeAttribute) {
            throw new InvalidArgumentException('The given value is not instance of IsOfflineDatabaseMode Attribute');
        }
        return [
            'is_offline_database_mode' => $value->getValue()
        ];
    }
}
