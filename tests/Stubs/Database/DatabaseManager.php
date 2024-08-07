<?php

namespace Tests\Stubs\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;
use InvalidArgumentException;

class DatabaseManager extends IlluminateDatabaseManager
{
    protected $connections = [];

    public function __construct()
    {
    }

    /**
     * @param string|null $name
     * @throws InvalidArgumentException
     */
    public function connection($name = null)
    {
        if (!$name) {
            return $this->connection('default');
        }

        if (!isset($this->connections[$name])) {
            throw new InvalidArgumentException("Database [$name] not configured.");
        }

        return $this->connections[$name];
    }

    public function addConnection(string $name, Connection $connection): void
    {
        $this->connections[$name] = $connection;
    }
}
