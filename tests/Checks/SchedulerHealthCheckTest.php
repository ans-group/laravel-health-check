<?php

namespace Tests\Checks;

use Exception;
use Illuminate\Foundation\Application;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class SchedulerHealthCheckTest extends TestCase
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

    public function testShowsProblemIfNoItemFoundInCache(): void
    {
        config([
            'healthcheck.scheduler.cache-key' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        Cache::shouldReceive('has')->andReturn(false);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
        $this->assertEquals('Scheduler has not ran in the last 5 minutes', $status->message());
    }

    public function testShowsOkayIfItemFoundInCache(): void
    {
        config([
            'healthcheck.scheduler.cache-key' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        Cache::shouldReceive('has')->andReturn(true);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }
}
