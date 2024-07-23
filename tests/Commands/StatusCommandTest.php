<?php

namespace Tests\Commands;

use Illuminate\Testing\PendingCommand;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Status;

class StatusCommandTest extends TestCase
{
    public function testRunningCommandStatus(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);

        $status = new Status();
        $status->okay();
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status');

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(0);
    }

    public function testRunningCommandStatusWithOnlyOption(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $status = new Status();
        $status->okay();
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status', ['--only' => 'log']);

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(0);
    }

    public function testRunningCommandStatusWithExceptOption(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class, DatabaseHealthCheck::class]]);

        $status = new Status();
        $status->okay();
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status', ['--except' => 'database']);

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(0);
    }

    public function testRunningCommandStatusWithOnlyAndExceptOption(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $result = $this->artisan('health-check:status', ['--only' => 'log', '--except' => 'log']);

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(1)
            ->expectsOutput('Pass --only OR --except, but not both!');
    }

    public function testRunningCommandStatusWithFailureCondition(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);
        $status = new Status();
        $status->withName('statusName')->problem('statusMessage');
        $this->mockLogHealthCheck($status);

        $result = $this->artisan('health-check:status');

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(1);
        $result->expectsTable(['name', 'status', 'message'], [['log', 'statusName', 'statusMessage']]);
    }

    private function mockLogHealthCheck(Status $status): void
    {
        $this->instance(
            LogHealthCheck::class,
            Mockery::mock(LogHealthCheck::class, function (MockInterface $mock) use ($status) {
                $mock->shouldReceive('name')->andReturn('log');
                $mock->shouldReceive('status')->andReturn($status);
            })
        );
    }
}
