<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Tests\Stubs\Database\BadConnection;
use Tests\Stubs\Database\DatabaseManager;
use Tests\Stubs\Database\HealthyConnection;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class DatabaseHealthCheckTest extends TestCase
{
    /**
     * @inheritDoc
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

        $database = new DatabaseManager();
        $database->addConnection('default', new BadConnection());

        $status = (new DatabaseHealthCheck($database))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayWhenCanConnectToDb(): void
    {
        config([
            'healthcheck.database.connections' => ['default'],
        ]);

        $database = new DatabaseManager();
        $database->addConnection('default', new HealthyConnection());

        $status = (new DatabaseHealthCheck($database))->status();

        $this->assertTrue($status->isOkay());
    }

    public function testShowsWhichConnectionFailed(): void
    {
        config([
            'healthcheck.database.connections' => ['healthy', 'bad'],
        ]);

        $database = new DatabaseManager();
        $database->addConnection('healthy', new HealthyConnection());
        $database->addConnection('bad', new BadConnection());

        $status = (new DatabaseHealthCheck($database))->status();

        $this->assertTrue($status->isProblem());
        $this->assertSame('bad', $status->context()['connection']);
    }
}
