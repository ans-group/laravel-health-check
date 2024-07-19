<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use Exception;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class LogHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsProblemIfCannotWriteToLogs(): void
    {
        $this->app->bind('log', function () {
            return new BadLogger;
        });

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayIfCanWriteToLogs(): void
    {
        $this->app->bind('log', function () {
            return new NullLogger;
        });

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isOkay());
    }
}

class BadLogger
{
    /**
     * @throws Exception
     */
    public function __call($name, $args): never
    {
        throw new Exception('Failed to log');
    }
}

class NullLogger
{
    public function __call($name, $args): VOID
    {
    }
}
