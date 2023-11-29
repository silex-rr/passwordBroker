<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\FileName as FileNameAttribute;

class FileName implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new FileNameAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof FileNameAttribute) {
            throw new InvalidArgumentException('The given value is not instance of FileName Attribute');
        }
        return [
            'file_name' => $value->getValue()
        ];
    }
}
