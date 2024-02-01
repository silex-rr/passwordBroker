<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\BackupPassword as BackupPasswordAttribute;

class BackupPassword implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new BackupPasswordAttribute($value ? Crypt::decryptString($value) : null);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof BackupPasswordAttribute) {
            throw new InvalidArgumentException('The given value is not instance of BackupPassword Attribute');
        }
        return [
            'password' => $value->getValue() ? Crypt::encryptString($value->getValue()) : null,
        ];
    }
}
