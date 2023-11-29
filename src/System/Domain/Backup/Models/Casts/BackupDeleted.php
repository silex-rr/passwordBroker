<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\BackupCreated as BackupCreatedAttribute;
use System\Domain\Backup\Models\Attributes\BackupDeleted as BackupDeletedAttribute;

class BackupDeleted implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new BackupDeletedAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof BackupDeletedAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Backup.BackupDeleted Attribute');
        }
        return [
            'backup_deleted' => $value->getValue(),
        ];
    }
}
