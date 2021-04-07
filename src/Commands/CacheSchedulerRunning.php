<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;
use Carbon\Carbon;
class CacheSchedulerRunning extends Command
{
    /**
     * @var string
     */
    protected $signature = 'health-check:cache-scheduler-running';

    /**
     * @var string
     */
    protected $description = 'Caches the scheduler has just ran';

    public function handle()
    {
        $cacheKey = config('healthcheck.scheduler.cache-key');
        $cacheMinutes = config('healthcheck.scheduler.minutes-between-checks');

        Cache::put($cacheKey, 'healthy', Carbon::now()->addMinutes($cacheMinutes));
    }
}
