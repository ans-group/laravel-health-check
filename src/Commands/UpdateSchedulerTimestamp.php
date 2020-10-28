<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
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
        Storage::put(config('healthcheck.scheduler.timestamp-filename'), time());
    }
}
