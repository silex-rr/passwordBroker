<?php

namespace App\Common\Domain\Abstractions;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class FactoryDomain extends Factory
{
    public function modelName()
    {
        $resolver = static::$modelNameResolver ?? function () {
            $nameSpacePath = explode('Infrastructure\Factories', $this->getClassNamespace());
            $modelClass = $nameSpacePath[0] . 'Domain' . $nameSpacePath[1] . '\Models'
                . '\\' . Str::replace('Factory', '', $this->getClassShortName());
            $this->model = $modelClass;

            return $modelClass;
        };

        return $this->model ?? $resolver($this);
    }

    private function getClassNamespace(): string
    {
        return (new ReflectionClass(get_class($this)))->getNamespaceName();
    }

    private function getClassShortName(): string
    {
        return (new ReflectionClass(get_class($this)))->getShortName();
    }
}
