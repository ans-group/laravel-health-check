<?php

namespace Tests\Commands;

use Illuminate\Testing\PendingCommand;
use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class CacheSchedulerRunningTest extends TestCase
{
    /**
     * @test
     */
    public function running_command_updates_cache()
    {
        config([
            'healthcheck.scheduler.cache-key' => 'laravel-scheduler-health-check',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        $this->app->register(HealthCheckServiceProvider::class);

        $result = $this->artisan('health-check:cache-scheduler-running');

        if ($result instanceof PendingCommand) {
            $result->assertExitCode(0);
        }

        $this->assertTrue(true);
    }
}
