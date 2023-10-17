<?php

namespace Identity\Domain\UserApplication\Models\Casts;

use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate as IsRsaPrivateRequiredUpdateAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class IsRsaPrivateRequiredUpdate implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new IsRsaPrivateRequiredUpdateAttribute(is_bool($value) ? $value : (bool)$value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof IsRsaPrivateRequiredUpdateAttribute) {
            throw new InvalidArgumentException('The given value is not instance of IsRsaPrivateRequiredUpdate Attribute');
        }
        return [
            'is_rsa_private_required_update' => $value->getValue()
        ];
    }
}
