<?php

namespace App\Common\Application\Traits;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

trait RequestAllWithCasts
{
    public function allWithCasts(): array
    {
        $class_name = $this->getModel();
        $model = app($class_name);
        if (!$model instanceof Model) {
            throw new \InvalidArgumentException($class_name . ' is not instance of Model');
        }
        $out = [];
        $casts = $model->getCasts();
        $all = $this->all();
        foreach ($all as $key => $value) {
            if (is_null($value)) {
                $out[$key] = $value;
                continue;
            }
            if (array_key_exists($key, $casts)) {
                if (in_array($casts[$key], ['int', 'string', 'float', 'datetime'])) {
                    switch ($casts[$key]) {
                        case 'int':
                            $out[$key] = (int)$value;
                            break;
                        case 'datetime':
                        case 'string':
                            $out[$key] = (string)$value;
                            break;
                        case 'float':
                            $out[$key] = (float)$value;
                            break;
                    }
                    continue;
                }

                /**
                 * @var CastsAttributes $cast
                 */
                $cast = new $casts[$key];

                $out[$key] = $cast->get($model, $key, $value, $all);
                continue;
            }
            $out[$key] = $value;
        }
        return $out;
    }

    /**
     * Require interface App\Common\Application\Contracts\CastsModelInterface
     * @return string
     */
    abstract public function getModel(): string;
}
