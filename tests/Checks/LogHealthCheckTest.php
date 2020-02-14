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

    /**
     * @test
     */
    public function shows_problem_if_cannot_write_to_logs()
    {
        $this->app->bind('log', function () {
            return new BadLogger;
        });

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_okay_if_can_write_to_logs()
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