<?php

namespace Tests\Checks;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class DatabaseHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsProblemWhenCantConnectToDb(): void
    {
        config([
            'healthcheck.database.connections' => ['default'],
        ]);

        $db = new DatabaseManager;
        $db->addConnection('default', new BadConnection);

        $status = (new DatabaseHealthCheck($db))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayWhenCanConnectToDb(): void
    {
        config([
            'healthcheck.database.connections' => ['default'],
        ]);

        $db = new DatabaseManager;
        $db->addConnection('default', new HealthyConnection);

        $status = (new DatabaseHealthCheck($db))->status();

        $this->assertTrue($status->isOkay());
    }

    public function testShowsWhichConnectionFailed(): void
    {
        config([
            'healthcheck.database.connections' => ['healthy', 'bad'],
        ]);

        $db = new DatabaseManager;
        $db->addConnection('healthy', new HealthyConnection);
        $db->addConnection('bad', new BadConnection);

        $status = (new DatabaseHealthCheck($db))->status();

        $this->assertTrue($status->isProblem());
        $this->assertSame('bad', $status->context()['connection']);
    }
}

class HealthyConnection extends Connection
{
    public function __construct()
    {
    }

    public function getPdo(): bool
    {
        return true;
    }
}

class BadConnection extends Connection
{
    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function getPdo(): never
    {
        throw new Exception;
    }
}

class DatabaseManager extends IlluminateDatabaseManager
{
    protected $connections = [];

    public function __construct()
    {
    }

    /**
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

    public function addConnection($name, $connection)
    {
        $this->connections[$name] = $connection;
    }
}
