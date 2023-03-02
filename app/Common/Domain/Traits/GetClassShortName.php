<?php

namespace App\Common\Domain\Traits;

trait GetClassShortName
{
    protected static function getClassShortName(): string
    {
        return (new \ReflectionClass(self::class))->getShortName();
    }
}
