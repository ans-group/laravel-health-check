<?php

namespace Tests\Checks;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
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
    public function shows_problem_if_no_timestamp_file_exists()
    {
        config([
            'healthcheck.scheduler.timestamp-filename' => 'laravel-scheduler-health-check.txt',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        Storage::shouldReceive('exists')->andReturn(false);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
        $this->assertEquals('Scheduler has not ran yet', $status->message());
    }

    /**
     * @test
     */
    public function shows_problem_if_timestamp_file_is_over_a_minute_old()
    {
        config([
            'healthcheck.scheduler.timestamp-filename' => 'laravel-scheduler-health-check.txt',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        $now = time();
        $tenMinutesAgo = $now - (60 * 10);

        Storage::shouldReceive('exists')->andReturn(true)
            ->shouldReceive('get')->andReturn($tenMinutesAgo);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
        $this->assertEquals('Scheduler last ran more than 5 minute ago', $status->message());

        $this->assertEquals(date('Y-m-d H:i:s', $tenMinutesAgo), $status->context()['last_ran']);
        $this->assertEquals(date('Y-m-d H:i:s', $now), $status->context()['now']);
        $this->assertEquals(600, $status->context()['seconds_since_scheduler_ran']);   
    }

    /**
     * @test
     */
    public function shows_okay_if_timestamp_file_is_under_a_minute_old()
    {
        config([
            'healthcheck.scheduler.timestamp-filename' => 'laravel-scheduler-health-check.txt',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);
        
        $now = time();
        $tenSecondsAgo = $now - 10;

        Storage::shouldReceive('exists')->andReturn(true)
            ->shouldReceive('get')->andReturn($tenSecondsAgo);

        $status = (new SchedulerHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }
}
