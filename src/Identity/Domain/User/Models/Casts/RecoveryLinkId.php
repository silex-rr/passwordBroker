<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\RecoveryLinkId as RecoveryLinkIdAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class RecoveryLinkId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): RecoveryLinkIdAttribute
    {
        return new RecoveryLinkIdAttribute($value?:null);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof RecoveryLinkIdAttribute) {
            throw new InvalidArgumentException("The value is not an instance of RecoveryLinkId Attribute.");
        }
        return [
            'recovery_link_id' => $value->getValue()
        ];
    }
}
