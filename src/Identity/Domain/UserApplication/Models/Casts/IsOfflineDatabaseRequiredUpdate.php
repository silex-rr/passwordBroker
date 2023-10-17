<?php

namespace Identity\Domain\UserApplication\Models\Casts;

use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate as IsOfflineDatabaseRequiredUpdateAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class IsOfflineDatabaseRequiredUpdate implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new IsOfflineDatabaseRequiredUpdateAttribute(is_bool($value) ? $value : (bool)$value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof IsOfflineDatabaseRequiredUpdateAttribute) {
            throw new InvalidArgumentException('The given value is not instance of IsOfflineDatabaseRequiredUpdate Attribute');
        }
        return [
            'is_offline_database_required_update' => $value->getValue()
        ];
    }
}
