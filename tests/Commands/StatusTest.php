<?php

namespace Tests\Commands;

use Mockery\MockInterface;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Status;

class StatusTest extends TestCase
{
    /**
     * @test
     */
    public function running_command_status()
    {
        config(['healthcheck.checks' => [LogHealthCheck::class]]);
        $this->app->register(HealthCheckServiceProvider::class);

        $this->mock(LogHealthCheck::class, function (MockInterface $mock) {
            $status = new Status();
            $status->okay();

            $mock->shouldReceive('status')->andReturn($status);
        });

        $this
            ->artisan('health-check:status')
            ->assertExitCode(0)
        ;
    }

    /**
     * @test
     */
    public function running_command_status_with_failure_condition()
    {
        config(['healthcheck.checks' => [LogHealthCheck::class]]);
        $this->app->register(HealthCheckServiceProvider::class);

        $this->mock(LogHealthCheck::class, function (MockInterface $mock) {
            $status = new Status();
            $status->withName('statusName')->problem('statusMessage');

            $mock->shouldReceive('name')->andReturn('log');
            $mock->shouldReceive('status')->andReturn($status);
        });

        $this
            ->artisan('health-check:status')
            ->assertExitCode(1)
            ->expectsTable(['name', 'status', 'message'], [['log', 'statusName', 'statusMessage']])
        ;
    }
}
