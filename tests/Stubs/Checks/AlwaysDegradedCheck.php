<?php

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class AlwaysDegradedCheck extends HealthCheck
{
    protected string $name = 'always-degraded';

    public function status(): Status
    {
        return $this->degraded('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}
