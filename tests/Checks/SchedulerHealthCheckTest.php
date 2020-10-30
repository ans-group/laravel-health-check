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

    /**
     * @test
     */
    public function shows_problem_if_no_item_found_in_cache()
    {
        config([
            'healthcheck.scheduler.cache-key' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        Cache::shouldReceive('exists')->andReturn(false);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
        $this->assertEquals('Scheduler has not ran in the last 5 minutes', $status->message());
    }

    /**
     * @test
     */
    public function shows_okay_if_item_found_in_cache()
    {
        config([
            'healthcheck.scheduler.cache-key' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);
        
        Cache::shouldReceive('exists')->andReturn(true);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }
}
