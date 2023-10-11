<?php

namespace App\Common\Domain\Traits;

use Illuminate\Database\Eloquent\Model;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

trait AppendGlobal
{
    protected static array $appendsGlobal = [

    ];
    public static function boot(): void
    {
        parent::boot();

        static::retrieved(static fn (Model $model)
            => $model->appends = array_unique(array_merge($model->appends, static::$appendsGlobal))
        );
    }

    protected static function appendField($field): void
    {
        if (in_array($field, static::$appendsGlobal, true)) {
            return;
        }
        static::$appendsGlobal[] = $field;
    }

    abstract public static function retrieved($callback);
}
