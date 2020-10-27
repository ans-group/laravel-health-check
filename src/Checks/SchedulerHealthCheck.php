<?php

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;
use Illuminate\Support\Facades\Storage;

class SchedulerHealthCheck extends HealthCheck
{
    protected $name = 'scheduler';
    
    public function status()
    {
        $filename = config('healthcheck.scheduler.timestamp-filename');
        $minutesBetweenChecks = config('healthcheck.scheduler.minutes-between-checks');

        if (!Storage::exists($filename)) {
            return $this->problem('Scheduler has not ran yet');
        }

        $schedulerLastRan = Storage::get($filename);
        $now = time();

        $secondsSinceSchedulerRan = $now - $schedulerLastRan;

        if ($secondsSinceSchedulerRan < (60 * $minutesBetweenChecks)) {
            return $this->okay();
        }

        return $this->problem("Scheduler last ran more than $minutesBetweenChecks minute ago", [
            'last_ran' => date('Y-m-d H:i:s', $schedulerLastRan),
            'now' => date('Y-m-d H:i:s', $now),
            'seconds_since_scheduler_ran' => $secondsSinceSchedulerRan,
        ]);
    }
}
