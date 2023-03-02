<?php

namespace App\Common\Domain\Traits;

use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Str;

Trait ModelDomainConstructor
{
    use GetClassNamespace;
    public function __construct(array $attributes = array())
    {
        $app = Str::snake(explode('\\', self::getClassNamespace())[0]);
        $this->getConnection()->setTablePrefix($app . '_');

        parent::__construct($attributes);
    }
}
