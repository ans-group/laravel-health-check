<?php

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;
use Illuminate\Support\Facades\Storage;

class SchedulerHealthCheck extends HealthCheck
{
    protected $name = 'scheduler';

    const FILE_NAME = 'laravel-scheduler-health-check.txt';
    
    public function status()
    {
        if (!Storage::exists(static::FILE_NAME)) {
            return $this->problem('Scheduler has not ran yet');
        }

        $schedulerLastRan = Storage::get(static::FILE_NAME);
        $now = time();

        $secondsSinceSchedulerRan = $now - $schedulerLastRan;

        if ($secondsSinceSchedulerRan < 60) {
            return $this->okay();
        }

        return $this->problem('Scheduler last ran more than 1 minute ago', [
            'scheduler_last_ran' => $schedulerLastRan,
            'now' => $now,
            'time_since_scheduler_ran' => $secondsSinceSchedulerRan,
        ]);
    }
}
