<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;

class UpdateSchedulerTimestamp extends Command
{
    /**
     * @var string
     */
    protected $signature = 'health-check:update-scheduler-timestamp';

    /**
     * @var string
     */
    protected $description = 'Updates the timestamp of when the scheduler last ran';

    public function handle()
    {
        $cacheKey = config('healthcheck.scheduler.cache-key');
        $cacheMinutes = config('healthcheck.scheduler.minutes-between-checks');

        Cache::put($cacheKey, 'healthy', (60 * $cacheMinutes));
    }
}