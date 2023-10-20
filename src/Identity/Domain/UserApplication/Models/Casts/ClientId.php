<?php

namespace Identity\Domain\UserApplication\Models\Casts;

use Identity\Domain\UserApplication\Models\Attributes\ClientId as ClientIdAttribute;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class ClientId implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): ClientIdAttribute
    {
        return new ClientIdAttribute($value ?: null);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (! $value instanceof ClientIdAttribute) {
            throw new InvalidArgumentException("The value is not an instance of ClientId Attribute.");
        }
        return [
            'client_id' => $value->getValue()
        ];
    }
}
