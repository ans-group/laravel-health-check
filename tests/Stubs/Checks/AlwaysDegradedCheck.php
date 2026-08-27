<?php

declare(strict_types=1);

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\HealthCheck;

class AlwaysDegradedCheck extends HealthCheck
{
    protected string $name = 'always-degraded';

    public function check(): void
    {
        $this->degrade('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}
