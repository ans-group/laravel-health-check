<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Listeners;

use UKFast\HealthCheck\HealthCheckRunner;

class RunPackageHealthChecks
{
    public function handle(): void
    {
        app(HealthCheckRunner::class)->run();
    }
}
