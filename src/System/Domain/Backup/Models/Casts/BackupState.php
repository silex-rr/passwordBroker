<?php

namespace System\Domain\Backup\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use System\Domain\Backup\Models\Attributes\BackupState as BackupStateAttribute;

class BackupState implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return BackupStateAttribute::from($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof BackupStateAttribute) {
            throw new InvalidArgumentException('The given value is not instance of Setting.BackupState Attribute');
        }
        return [
            'state' => $value->value,
        ];
    }
}
