<?php

declare(strict_types=1);

namespace Tests\Commands;

use Illuminate\Testing\PendingCommand;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\Exceptions\HealthCheckFailedException;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class StatusCommandTest extends TestCase
{
    public function testRunningCommandStatus(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class]]);

        $this->mockLogHealthCheck();

        $result = $this->artisan('health-check:status');

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(0);
    }

    public function testRunningCommandStatusWithOnlyOption(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->mockLogHealthCheck();

        $result = $this->artisan('health-check:status', ['--only' => 'log']);

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(0);
    }

    public function testRunningCommandStatusWithExceptOption(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        config(['healthcheck.checks' => [LogHealthCheck::class, DatabaseHealthCheck::class]]);

        $this->mockLogHealthCheck();

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
        $this->mockLogHealthCheck(new HealthCheckFailedException('log', 'statusMessage'));

        $result = $this->artisan('health-check:status');

        $this->assertInstanceof(PendingCommand::class, $result);
        $result->assertExitCode(1);
        $result->expectsTable(['name', 'status', 'message'], [['log', 'down', 'statusMessage']]);
    }

    private function mockLogHealthCheck(HealthCheckFailedException|null $failure = null): void
    {
        $this->instance(
            LogHealthCheck::class,
            Mockery::mock(LogHealthCheck::class, function (MockInterface $mock) use ($failure): void {
                $mock->shouldReceive('name')->andReturn('log');
                if ($failure instanceof HealthCheckFailedException) {
                    $mock->shouldReceive('check')->andThrow($failure);

                    return;
                }

                $mock->shouldReceive('check')->andReturnNull();
            })
        );
    }
}
