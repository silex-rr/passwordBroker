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
        $domain = Str::snake(explode('\\', self::getClassNamespace())[0]);
        $this->setTable($domain . '_' . $this->getTable());
//        dd($domain, __CLASS__, static::class, $this->getTable());
//        $this->getConnection()->setTablePrefix($connection_name . '_');
//        $this->setConnection($connection_name);

        parent::__construct($attributes);
    }
}
