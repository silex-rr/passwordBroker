<?php

namespace App\Common\Domain\Traits;

trait EnumToArray
{
    public static function toArray(): array
    {
        return array_reduce(self::cases(),
            static function (array $array, $value) {
                $array[$value->name] = $value->value;;

                return $array;
            }, []);
    }
}
