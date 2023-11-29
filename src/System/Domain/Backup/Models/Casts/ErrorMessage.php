<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\ErrorMessage as ErrorMessageAttribute
    ;

class ErrorMessage implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new ErrorMessageAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof ErrorMessageAttribute) {
            throw new InvalidArgumentException('The given value is not instance of ErrorMessageAttribute');
        }
        return [
            'error_message' => $value->getValue()
        ];
    }
}
