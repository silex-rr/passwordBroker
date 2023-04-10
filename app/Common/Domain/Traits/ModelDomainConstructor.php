<?php

namespace App\Common\Domain\Traits;

use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Str;

Trait ModelDomainConstructor
{
    use GetClassNamespace;

    use ModelDomainTableFullName;
    public function __construct(array $attributes = array())
    {
        $connection_name = Str::snake(explode('\\', self::getClassNamespace())[0]);
//        $this->getConnection()->setTablePrefix($connection_name . '_');
        $this->setConnection($connection_name);

        parent::__construct($attributes);
    }
}
