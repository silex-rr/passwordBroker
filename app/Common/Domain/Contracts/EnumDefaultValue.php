<?php

namespace App\Common\Domain\Contracts;

interface EnumDefaultValue
{
    public static function default(): self;
}
