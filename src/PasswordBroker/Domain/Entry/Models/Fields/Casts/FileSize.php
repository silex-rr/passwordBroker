<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileName as FIleNameAttribute;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileSize as FIleSizeAttribute;

class FileSize implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new FIleSizeAttribute((int)$value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (! $value instanceof FIleSizeAttribute) {
            throw new InvalidArgumentException('The given value is not instance of File Size Attribute');
        }

        return [
            'file_size' => $value->getValue()
        ];
    }
}
