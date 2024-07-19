<?php

namespace Tests\Checks;

use Tests\TestCase;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use Exception;

class LogHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    public function testShowsProblemIfCannotWriteToLogs()
    {
        $this->app->bind('log', function () {
            return new BadLogger;
        });

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayIfCanWriteToLogs()
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
    public function __call($name, $args)
    {
        throw new Exception('Failed to log');
    }
}

class NullLogger
{
    public function __call($name, $args)
    {
    }
}