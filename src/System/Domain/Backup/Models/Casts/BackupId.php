<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\BackupId as BackupIdAttribute;

class BackupId implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return new BackupIdAttribute($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof BackupIdAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Setting.BackupId Attribute');
        }
        return [
            'backup_id' => $value->getValue(),
        ];
    }
}
