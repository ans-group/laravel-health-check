<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;
use Illuminate\Support\Facades\Cache;

class SchedulerHealthCheck extends HealthCheck
{
    protected string $name = 'scheduler';

    public function check(): void
    {
        $cacheKey = config('healthcheck.scheduler.cache-key');
        $minutesBetweenChecks = config('healthcheck.scheduler.minutes-between-checks');

        if (!Cache::has($cacheKey)) {
            $this->fail("Scheduler has not ran in the last $minutesBetweenChecks minutes");
        }
    }
}
