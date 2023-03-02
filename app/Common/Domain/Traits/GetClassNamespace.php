<?php

namespace App\Common\Domain\Traits;

trait GetClassNamespace
{
    protected static function getClassNamespace(): string
    {
        return (new \ReflectionClass(self::class))->getNamespaceName();
    }
}
