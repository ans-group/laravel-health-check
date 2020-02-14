<?php

namespace Tests\Checks;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolver;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;

class DatabaseHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_problem_when_cant_connect_to_db()
    {
        config([
            'healthcheck.database.connections' => ['default'],
        ]);

        $db = new DatabaseManager;
        $db->addConnection('default', new BadConnection);

        $status = (new DatabaseHealthCheck($db))->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_okay_when_can_connect_to_db()
    {
        config([
            'healthcheck.database.connections' => ['default'],
        ]);

        $db = new DatabaseManager;
        $db->addConnection('default', new HealthyConnection);

        $status = (new DatabaseHealthCheck($db))->status();

        $this->assertTrue($status->isOkay());
    }

    /**
     * @test
     */
    public function shows_which_connection_failed()
    {
        config([
            'healthcheck.database.connections' => ['healthy', 'bad'],
        ]);

        $db = new DatabaseManager;
        $db->addConnection('healthy', new HealthyConnection);
        $db->addConnection('bad', new BadConnection);

        $status = (new DatabaseHealthCheck($db))->status();

        $this->assertTrue($status->isProblem());
        $this->assertEquals('bad', $status->context()['connection']);
    }
}

class HealthyConnection extends Connection
{
    public function __construct()
    {
    }

    public function getPdo()
    {
        return true;
    }
}

class BadConnection extends Connection
{
    public function __construct()
    {
    }

    public function getPdo()
    {
        throw new Exception;
    }
}

class DatabaseManager extends \Illuminate\Database\DatabaseManager
{
    protected $connections = [];

    public function __construct()
    {
    }

    public function connection($name = null)
    {
        if (!$name) {
            return $this->connection('default');
        }
        
        if (!isset($this->connections[$name])) {
            throw new \InvalidArgumentException("Database [$name] not configured.");
        }

        return $this->connections[$name];
    }

    public function addConnection($name, $connection)
    {
        $this->connections[$name] = $connection;
    }
}