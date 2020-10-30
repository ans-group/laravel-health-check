<?php

namespace Tests\Commands;

use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Illuminate\Support\Facades\Cache;

class UpdateSchedulerTimestampTest extends TestCase
{
    /**
     * @test
     */
    public function running_command_updates_cache()
    {
        Cache::shouldReceive('put')
            ->with('laravel-scheduler-health-check', 'healthy', (5 * 60))
            ->once()
            ->andReturnSelf();

        config([
            'healthcheck.scheduler.timestamp-filename' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        $this->app->register(HealthCheckServiceProvider::class);

        $this->artisan('health-check:update-scheduler-timestamp');        
    }
}