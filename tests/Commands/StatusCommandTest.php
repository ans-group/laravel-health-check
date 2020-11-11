<?php

namespace Tests\Commands;

use Illuminate\Testing\PendingCommand;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Status;

class StatusCommandTest extends TestCase
{
    /**
     * @test
     */
    public function running_command_status()
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);

        $status = new Status();
        $status->okay();
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status');

        if ($result instanceof PendingCommand) {
            $result->assertExitCode(0);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     */
    public function running_command_status_with_disable_option()
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);

        $status = new Status();
        $status->problem();
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status', ['--disable' => 'log']);

        if ($result instanceof PendingCommand) {
            $result->assertExitCode(0);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     */
    public function running_command_status_with_failure_condition()
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);
        $status = new Status();
        $status->withName('statusName')->problem('statusMessage');
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status');

        if ($result instanceof PendingCommand) {
            $result->assertExitCode(1);

            //for laravel 5.*
            if (method_exists($result, 'expectsTable')) {
                $result->expectsTable(['name', 'status', 'message'], [['log', 'statusName', 'statusMessage']]);
            }
        }
    }

    private function mockLogHealthCheck(Status $status)
    {
        $this->instance(
            LogHealthCheck::class,
            Mockery::mock(LogHealthCheck::class, function (MockInterface $mock) use ($status) {
                $mock->shouldReceive('name')->andReturn('log');
                $mock->shouldReceive('status')->andReturn($status);
            }));
    }
}
