<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\BackupCreated as BackupCreatedAttribute;

class BackupCreated implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new BackupCreatedAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof BackupCreatedAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Backup.BackupCreated Attribute');
        }
        return [
            'backup_created' => $value->getValue(),
        ];
    }
}
