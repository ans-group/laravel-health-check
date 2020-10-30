<?php

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;
use Illuminate\Support\Facades\Cache;

class SchedulerHealthCheck extends HealthCheck
{
    protected $name = 'scheduler';
    
    public function status()
    {
        $cacheKey = config('healthcheck.scheduler.cache-key');
        $minutesBetweenChecks = config('healthcheck.scheduler.minutes-between-checks');

        if (!Cache::has($cacheKey)) {
            return $this->problem("Scheduler has not ran in the last $minutesBetweenChecks minutes");
        }
        
        return $this->okay();
    }
}
