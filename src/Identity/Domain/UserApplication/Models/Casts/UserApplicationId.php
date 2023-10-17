<?php

namespace Identity\Domain\UserApplication\Models\Casts;

use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId as UserApplicationIdAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class UserApplicationId implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): UserApplicationIdAttribute
    {
        return new UserApplicationIdAttribute($value ?: null);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof UserApplicationIdAttribute) {
            throw new InvalidArgumentException("The value is not an instance of UserApplicationId Attribute.");
        }
        return [
            'user_application_id' => $value->getValue()
        ];
    }
}
