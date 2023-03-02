<?php

namespace Identity\Domain\User\Models\Casts;

use Identity\Domain\User\Models\Attributes\UserId as UserIdAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class UserId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): UserIdAttribute
    {
        return new UserIdAttribute($value?:null);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof UserIdAttribute) {
            throw new InvalidArgumentException("The value is not an instance of UserId Attribute.");
        }
        return [
            'user_id' => $value->getValue()
        ];
    }
}
