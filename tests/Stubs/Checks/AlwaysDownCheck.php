<?php

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class AlwaysDownCheck extends HealthCheck
{
    protected string $name = 'always-down';

    public function status(): Status
    {
        return $this->problem('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}
