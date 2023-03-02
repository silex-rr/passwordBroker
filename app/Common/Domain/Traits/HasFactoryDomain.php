<?php

namespace App\Common\Domain\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;
trait HasFactoryDomain
{
    use HasFactory;
    use GetClassShortName;
    use GetClassNamespace;

    protected static function newFactory()
    {
        $n_arr = explode('\\', self::getClassNamespace());

        $p_1 = array_search('Domain', $n_arr);
        $p_2 = array_search('Models', $n_arr);

        $replacement = array_slice($n_arr, $p_1 + 1, $p_2 - $p_1 - 1);
        array_unshift($replacement, 'Infrastructure\Factories');

        array_splice(
            $n_arr,
            $p_1,
            $p_2,
            $replacement
        );

        $factoryClass = implode('\\', $n_arr) . '\\' . self::getClassShortName() . 'Factory';

        return new $factoryClass;
    }
}
