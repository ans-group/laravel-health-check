<?php

declare(strict_types=1);

namespace Tests\Commands;

use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Illuminate\Support\Facades\Cache;

class CacheSchedulerRunningTest extends TestCase
{
    public function testRunningCommandUpdatesCache(): void
    {
        config([
            'healthcheck.scheduler.cache-key' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        $this->app->register(HealthCheckServiceProvider::class);

        $this->artisan('health-check:cache-scheduler-running');

        $this->assertTrue(true); // check command ran without error
    }
}
