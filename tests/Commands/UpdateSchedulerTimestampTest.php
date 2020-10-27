<?php

namespace Tests\Commands;

use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Illuminate\Support\Facades\Storage;

class UpdateSchedulerTimestampTest extends TestCase
{
    /**
     * @test
     */
    public function running_command_updates_timestamp_in_file()
    {
        Storage::fake();

        config([
            'healthcheck.scheduler.timestamp-filename' => 'laravel-scheduler-health-check.txt',
            'healthcheck.scheduler.minutes-between-checks' => 5,
        ]);

        $this->app->register(HealthCheckServiceProvider::class);

        $this->artisan('health-check:update-scheduler-timestamp')
            ->assertExitCode(0);

        Storage::assertExists('laravel-scheduler-health-check.txt');
        $this->assertEquals(time(), Storage::get('laravel-scheduler-health-check.txt'));
        
    }
}