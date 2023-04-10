<?php

namespace App\Common\Domain\Traits;

use Illuminate\Database\Connection;

trait ModelDomainTableFullName
{
    public function getTableFullName(): string
    {
        return  $this->getConnection()->getTablePrefix() . $this->getTable();
    }

    /**
     * @return string
     */
    abstract function getTable();

    /**
     * @return Connection
     */
    abstract function getConnection();

}
