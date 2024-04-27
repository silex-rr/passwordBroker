<?php

namespace App\Common\Domain\Traits;

trait EnumToArray
{
    public static function toArray(): array
    {
        return array_reduce(self::cases(),
            static function (array $array, $value) {
                $value[$array['name']] = $array[$value];

                return $array;
            }, []);
    }
}
