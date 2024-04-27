<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\Fingerprint as FingerprintAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class Fingerprint implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new FingerprintAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof FingerprintAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Setting. Fingerprint Attribute');
        }
        return [
            $key => $value->getValue(),
        ];
    }
}
