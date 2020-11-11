<?php

namespace Tests\Commands;

use Mockery;
use Mockery\Mock;
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
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);

        $this->instance(LogHealthCheck::class, Mockery::mock(LogHealthCheck::class, function (MockInterface $mock) {
            $status = new Status();
            $status->withName('statusName')->problem('statusMessage');

            $mock->shouldReceive('name')->andReturn('log');
            $mock->shouldReceive('status')->andReturn($status);
        }));

        $result = $this->artisan('health-check:status');
        $result->assertExitCode(1);

        //for laravel 5.*
        if (method_exists($result, 'expectsTable')) {
            $result->expectsTable(['name', 'status', 'message'], [['log', 'statusName', 'statusMessage']]);
        }
    }
}
