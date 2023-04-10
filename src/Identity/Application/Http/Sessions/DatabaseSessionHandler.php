<?php

namespace Identity\Application\Http\Sessions;

use App\Common\Domain\Traits\GetClassNamespace;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Session\DatabaseSessionHandler as DatabaseSessionHandlerOrigin;
use Illuminate\Support\Str;

class DatabaseSessionHandler extends DatabaseSessionHandlerOrigin
{
    use GetClassNamespace;

    private string $prefix;

    public function __construct(ConnectionInterface $connection, $table, $minutes, Container $container = null)
    {
        $this->prefix = Str::snake(explode('\\', self::getClassNamespace())[0]);

        parent::__construct($connection, $table, $minutes, $container);
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        $this->connection->setTablePrefix($this->prefix . '_');
        return $this->connection->table($this->table);
    }

}
