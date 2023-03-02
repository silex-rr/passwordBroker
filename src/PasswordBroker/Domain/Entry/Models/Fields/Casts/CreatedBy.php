<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Identity\Domain\User\Models\Attributes\UserId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class CreatedBy implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): UserId
    {
        return UserId::fromNative($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof UserId) {
            throw new InvalidArgumentException("The given value is not an instance of UserId Attribute");
        }

        return [
            'created_by' => $value->getValue()
        ];
    }
}
