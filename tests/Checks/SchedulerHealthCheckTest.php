<?php

namespace Tests\Checks;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;

class SchedulerHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    public function testShowsProblemIfNoItemFoundInCache()
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

    public function testShowsOkayIfItemFoundInCache()
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
